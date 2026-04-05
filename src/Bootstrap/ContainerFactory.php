<?php

declare(strict_types=1);

namespace App\Bootstrap;

use App\Config\AppConfig;
use App\View\FormFields;
use App\View\Vite;
use League\Container\Container;
use League\Container\ReflectionContainer;
use League\Plates\Engine;
use Monolog\Logger;
use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Slim\Csrf\Guard;
use Slim\Psr7\Factory\ResponseFactory;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;

final class ContainerFactory
{
	public function __construct(
		private readonly LoggerFactory $loggerFactory = new LoggerFactory(),
		private readonly PdoFactory $pdoFactory = new PdoFactory()
	) {
	}

	public function create(AppConfig $config): Container
	{
		$container = new Container();
		$container->delegate(new ReflectionContainer());

		$container->add(AppConfig::class, fn (): AppConfig => $config);
		$container->add(Engine::class, function () use ($config): Engine {
			$templates = new Engine($config->viewPath);
			$vite = new Vite(dirname(__DIR__, 2));
			$templates->registerFunction('vite', static fn (string $entrypoint): string => $vite->tags($entrypoint));
			$templates->registerFunction('csrfInputs', static fn (array $csrf): string => FormFields::csrfInputs($csrf));
			$templates->registerFunction('methodInput', static fn (string $method): string => FormFields::methodInput($method));
			return $templates;
		});
		$container->add(Logger::class, fn (): Logger => $this->loggerFactory->create($config));
		$container->add(LoggerInterface::class, fn (): LoggerInterface => $container->get(Logger::class));
		$container->add(PDO::class, fn (): PDO => $this->pdoFactory->create($config->database));
		$container->add(MailerInterface::class, fn (): MailerInterface => new Mailer(Transport::fromDsn($config->mailerDsn)));
		if (class_exists(Guard::class)) {
			$container->add(Guard::class, static function () use ($container): Guard {
				$responseFactory = new ResponseFactory();
				$failureHandler = static function (
					ServerRequestInterface $request,
					RequestHandlerInterface $handler
				) use ($responseFactory, $container): ResponseInterface {
					$statusCode = 403;
					$message = 'Your form has expired or failed security validation. Please refresh and try again.';
					$acceptHeader = strtolower($request->getHeaderLine('Accept'));
					$wantsJson = $acceptHeader !== '' && str_contains($acceptHeader, 'application/json');
					$response = $responseFactory->createResponse($statusCode);

					if ($wantsJson) {
						$response->getBody()->write((string) json_encode([
							'error' => [
								'status' => $statusCode,
								'code' => 'csrf_validation_failed',
								'message' => $message,
							],
						], JSON_THROW_ON_ERROR));

						return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
					}

					$templates = $container->get(Engine::class);
					$response->getBody()->write($templates->render('error', [
						'statusCode' => $statusCode,
						'title' => 'Security check failed',
						'message' => $message,
					]));

					return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
				};
				$storage = null;

				return new Guard($responseFactory, 'csrf', $storage, $failureHandler);
			});
		}

		return $container;
	}
}
