<?php

declare(strict_types=1);

namespace App\Action;

use App\Config\AppConfig;
use App\Config\RouteCatalog;
use League\Plates\Engine;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Interfaces\RouteCollectorInterface;

final class HomeAction
{
	public function __construct(
		private readonly Engine $templates,
		private readonly AppConfig $config,
		private readonly RouteCollectorInterface $routeCollector
	)
	{
	}

	public function __invoke(Request $request, Response $response): Response
	{
		$routes = RouteCatalog::dashboardRoutes($this->routeCollector, $this->config->debug);

		$response->getBody()->write($this->templates->render('home', [
			'title' => 'Welcome',
			'appName' => $this->config->appName,
			'routes' => $routes,
			'request' => [
				'method' => $request->getMethod(),
				'path' => $request->getUri()->getPath(),
				'host' => $request->getUri()->getHost(),
			],
			'system' => [
				'debug' => $this->config->debug ? 'enabled' : 'disabled',
				'phpVersion' => PHP_VERSION,
				'timezone' => date_default_timezone_get(),
				'mailerDsn' => $this->config->mailerDsn,
				'database' => $this->config->database->driver,
			],
		]));

		return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
	}
}
