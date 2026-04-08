<?php

declare(strict_types=1);

namespace App\Bootstrap;

use League\Plates\Engine;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Exception\HttpException;
use Slim\Exception\HttpNotFoundException;
use Throwable;

final class ErrorHandlerRegistrar
{
	public function register(App $app, ContainerInterface $container, bool $isDebug): void
	{
		$errorMiddleware = $app->addErrorMiddleware(
			$isDebug,
			true,
			true
		);

		$wantsJson = static function (ServerRequestInterface $request): bool {
			$forcedFormat = $request->getAttribute('error_response_format');
			if (is_string($forcedFormat)) {
				return strtolower($forcedFormat) === 'json';
			}

			$acceptHeader = strtolower($request->getHeaderLine('Accept'));
			if ($acceptHeader !== '' && str_contains($acceptHeader, 'application/json')) {
				return true;
			}

			return false;
		};

		$errorMiddleware->setErrorHandler(
			HttpNotFoundException::class,
			function (
				ServerRequestInterface $request,
				Throwable $exception,
				bool $displayErrorDetails,
				bool $logErrors,
				bool $logErrorDetails
			) use ($app, $container, $wantsJson): ResponseInterface {
				$response = $app->getResponseFactory()->createResponse(404);

				if ($wantsJson($request)) {
					$response->getBody()->write(json_encode([
						'error' => [
							'status' => 404,
							'message' => 'The requested resource could not be found.',
						],
					], JSON_THROW_ON_ERROR));

					return $response->withHeader('Content-Type', 'application/json');
				}

				$templates = $container->get(Engine::class);
				$response->getBody()->write($templates->render('404', [
					'title' => 'Page not found',
					'message' => 'The page you requested could not be found.',
				]));

				return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
			}
		);

		$errorMiddleware->setDefaultErrorHandler(
			function (
				ServerRequestInterface $request,
				Throwable $exception,
				bool $displayErrorDetails,
				bool $logErrors,
				bool $logErrorDetails
			) use ($app, $container, $isDebug, $wantsJson): ResponseInterface {
				if ($isDebug && !($exception instanceof HttpException)) {
					throw $exception;
				}

				$statusCode = $exception instanceof HttpException ? (int) $exception->getCode() : 500;
				if ($statusCode < 400 || $statusCode > 599) {
					$statusCode = 500;
				}

				$response = $app->getResponseFactory()->createResponse($statusCode);
				$message = $statusCode >= 500
					? 'An unexpected error occurred. Please try again later.'
					: 'Your request could not be processed.';

				if ($wantsJson($request)) {
					$response->getBody()->write(json_encode([
						'error' => [
							'status' => $statusCode,
							'message' => $message,
						],
					], JSON_THROW_ON_ERROR));

					return $response->withHeader('Content-Type', 'application/json');
				}

				$templates = $container->get(Engine::class);
				$response->getBody()->write($templates->render('error', [
					'statusCode' => $statusCode,
					'title' => $statusCode >= 500 ? 'Something went wrong' : 'Request failed',
					'message' => $message,
				]));

				return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
			}
		);
	}
}
