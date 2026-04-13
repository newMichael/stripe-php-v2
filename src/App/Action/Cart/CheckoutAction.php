<?php

declare(strict_types=1);

namespace App\Action\Cart;

use FORM\Ecommerce\Cart\Cart;
use FORM\Ecommerce\Cart\SessionCartStorage;
use League\Plates\Engine;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class CheckoutAction
{
	public function __construct(private readonly Engine $templates) {}

	public function __invoke(Request $request, Response $response): Response
	{
		$cart = new Cart(new SessionCartStorage());

		if ($cart->isEmpty()) {
			return $response->withHeader('Location', '/cart')->withStatus(303);
		}

		$response->getBody()->write(
			$this->templates->render('cart/checkout', [
				'stripePublishableKey' => $_ENV['STRIPE_PUBLIC_KEY'],
				'subtotal' => $cart->subtotal(),
			])
		);

		return $response;
	}
}
