<?php

declare(strict_types=1);

namespace App\Action\Cart;

use FORM\Ecommerce\Cart\Cart;
use FORM\Ecommerce\Cart\SessionCartStorage;
use League\Plates\Engine;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Flash\Messages;

final class ConfirmationAction
{
	public function __construct(private readonly Engine $templates, private readonly Messages $flash) {}

	public function __invoke(Request $request, Response $response): Response
	{
		$queryParams = $request->getQueryParams();
		$paymentIntentId = $queryParams['payment_intent'] ?? null;

		if ($paymentIntentId !== null) {
			try {
				$this->handleStripeRedirect($paymentIntentId);
			} catch (\Exception $e) {
				error_log('Error retrieving payment intent: ' . $e->getMessage());
				$this->flash->addMessage('confirmation_error', 'There was an issue confirming your payment. Please contact support if you were charged but did not receive a confirmation.');
			}
			return $response->withHeader('Location', '/cart/confirmation')->withStatus(303);
		}

		if (!isset($_SESSION['checkout_session'])) {
			return $response->withHeader('Location', '/cart')->withStatus(303);
		}

		$response->getBody()->write(
			$this->templates->render('cart/confirmation', [
				'errorMessage' => null,
			])
		);
		return $response;
	}

	private function handleStripeRedirect(string $paymentIntentId): void
	{
		$stripe = new \Stripe\StripeClient($_ENV['STRIPE_SECRET_KEY']);
		$paymentIntent = $stripe->paymentIntents->retrieve($paymentIntentId);

		if (in_array($paymentIntent->status, ['succeeded', 'processing'])) {
			$cart = new Cart(new SessionCartStorage());
			$cart->clear();
		}
		$_SESSION['checkout_session'] = [
			'payment_intent_id' => $paymentIntentId,
			'payment_intent_status' => $paymentIntent->status,
		];
	}
}
