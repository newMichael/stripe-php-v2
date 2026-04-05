<?php

declare(strict_types=1);

use App\Config\AppConfig;
use App\Bootstrap\ContainerFactory;
use App\Bootstrap\ErrorHandlerRegistrar;
use App\Bootstrap\MiddlewareRegistrar;
use App\Bootstrap\ProjectExtensionRegistrar;
use Dotenv\Dotenv;
use Slim\Factory\AppFactory;
use Slim\Interfaces\RouteCollectorInterface;
use Symfony\Component\ErrorHandler\Debug;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();
$config = AppConfig::fromEnvironment($_ENV, dirname(__DIR__));

if (session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}

$projectExtensions = require __DIR__ . '/../config/bootstrap.php';
$container = (new ContainerFactory())->create($config);

AppFactory::setContainer($container);
$app = AppFactory::create();
if (!$container->has(RouteCollectorInterface::class)) {
	$container->add(RouteCollectorInterface::class, static fn (): RouteCollectorInterface => $app->getRouteCollector());
}

(new ProjectExtensionRegistrar())->register($container, $app, $config, $projectExtensions);
(new MiddlewareRegistrar())->register($app);

$isDebug = $config->debug;
if ($isDebug) {
	Debug::enable();
}

(new ErrorHandlerRegistrar())->register($app, $container, $isDebug);

require __DIR__ . '/../src/routes.php';

return $app;
