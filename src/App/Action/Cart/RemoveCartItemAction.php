<?php

declare(strict_types=1);

namespace App\Action\Cart;

use FORM\Ecommerce\Cart\Cart;
use FORM\Ecommerce\Cart\SessionCartStorage;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class RemoveCartItemAction
{
	public function __invoke(Request $request, Response $response): Response
	{
		$body = (array) $request->getParsedBody();
		$key  = (string) ($body['key'] ?? '');

		if ($key !== '') {
			$cart = new Cart(new SessionCartStorage());
			$cart->remove($key);
		}

		return $response->withHeader('Location', '/cart')->withStatus(303);
	}
}
