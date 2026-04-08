<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Csrf\Guard;

final class CsrfViewDataMiddleware implements MiddlewareInterface
{
	public const ATTRIBUTE = 'csrf';

	public function __construct(private readonly Guard $csrf)
	{
	}

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$nameKey = $this->csrf->getTokenNameKey();
		$valueKey = $this->csrf->getTokenValueKey();

		$request = $request->withAttribute(self::ATTRIBUTE, [
			'name_key' => $nameKey,
			'value_key' => $valueKey,
			'name' => (string) ($request->getAttribute($nameKey) ?? ''),
			'value' => (string) ($request->getAttribute($valueKey) ?? ''),
		]);

		return $handler->handle($request);
	}
}
