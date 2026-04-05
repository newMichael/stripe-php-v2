<?php

declare(strict_types=1);

namespace App\Bootstrap;

use App\Middleware\CsrfViewDataMiddleware;
use Middlewares\TrailingSlash;
use Slim\App;
use Slim\Csrf\Guard;
use Slim\Middleware\MethodOverrideMiddleware;

final class MiddlewareRegistrar
{
	public function register(App $app): void
	{
		$container = $app->getContainer();
		if ($container !== null && $container->has(Guard::class)) {
			$guard = $container->get(Guard::class);
			$app->add(new CsrfViewDataMiddleware($guard));
			$app->add($guard);
		}

		$app->add((new TrailingSlash())->redirect());
		$app->addRoutingMiddleware();
		$app->add(new MethodOverrideMiddleware());
		$app->addBodyParsingMiddleware();
	}
}
