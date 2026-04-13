<?php

declare(strict_types=1);

namespace App\Action\Cart;

use FORM\Ecommerce\Cart\Cart;
use FORM\Ecommerce\Cart\SessionCartStorage;
use FORM\Ecommerce\Order\DraftOrderService;
use FORM\Ecommerce\Order\OrderRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Factory\StreamFactory;
use Stripe\StripeClient;

final class CheckoutIntentAction
{
	public function __construct(
		private readonly StripeClient $stripeClient,
		private readonly DraftOrderService $draftOrderService,
		private readonly OrderRepository $orderRepository,
	) {}

	public function __invoke(Request $request, Response $response): Response
	{
		$cart = new Cart(new SessionCartStorage());

		try {
			$order = $this->draftOrderService->createFromCart($cart);

			$stripe = $this->stripeClient;
			$paymentIntent = $stripe->paymentIntents->create([
				'amount' => (100 * $cart->subtotal()),
				'currency' => 'usd',
			]);
			$clientSecret = $paymentIntent->client_secret;

			$this->orderRepository->updatePaymentIntent($order->orderId, $paymentIntent->id);
		} catch (\Exception $e) {
			error_log('Error creating payment intent: ' . $e->getMessage());
			return $response->withStatus(500)->withHeader('Content-Type', 'application/json')
				->withBody((new StreamFactory())->createStream(json_encode(['error' => 'Failed to create payment intent'])));
		}

		$response->getBody()->write(json_encode([
			'clientSecret' => $clientSecret,
			'orderId' => $order->orderId,
		]));
		return $response->withHeader('Content-Type', 'application/json');
	}
}
