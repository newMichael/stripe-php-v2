<?php

declare(strict_types=1);

use App\Action\HomeAction;
use App\Action\SecurityDemoAction;
use App\Action\SendTestEmailAction;
use App\Action\UsersAction;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Routing\RouteCollectorProxy;

$app->get('/', HomeAction::class);
$app->get('/users', UsersAction::class);

$isDebug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOL);
if ($isDebug) {
	$app->group('/debug', function (RouteCollectorProxy $group): void {
		$group->get('/email/send', SendTestEmailAction::class);
		$group->get('/security-demo', SecurityDemoAction::class . ':form');
		$group->patch('/security-demo/resource', SecurityDemoAction::class . ':patch');
		$group->delete('/security-demo/resource', SecurityDemoAction::class . ':delete');

		$group->get('/error/html', function (ServerRequestInterface $request): never {
			throw new HttpBadRequestException($request, 'Debug HTML error route.');
		});

		$group
			->get('/error/json', function (ServerRequestInterface $request): never {
				throw new HttpBadRequestException($request, 'Debug JSON error route.');
			})
			->add(
				function (
					ServerRequestInterface $request,
					RequestHandlerInterface $handler
				): ResponseInterface {
					return $handler->handle($request->withAttribute('error_response_format', 'json'));
				}
			);
	});
}
