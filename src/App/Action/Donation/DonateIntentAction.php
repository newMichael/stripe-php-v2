<?php

declare(strict_types=1);

namespace App\Action\Donation;

use FORM\Ecommerce\Cart\ItemType;
use FORM\Ecommerce\Order\Order;
use FORM\Ecommerce\Order\OrderItem;
use FORM\Ecommerce\Order\OrderRepository;
use FORM\Ecommerce\Order\OrderStatus;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Factory\StreamFactory;
use Stripe\StripeClient;

final class DonateIntentAction
{
	public function __construct(
		private readonly StripeClient $stripeClient,
		private readonly OrderRepository $orderRepository,
	) {}

	public function __invoke(Request $request, Response $response): Response
	{
		$body = json_decode((string) $request->getBody(), true);
		$amountInCents = (int) ($body['amount'] ?? 0);
		$frequency = $body['frequency'] ?? 'one-time';
		$email = isset($body['email']) && $body['email'] !== '' ? (string) $body['email'] : null;

		if ($amountInCents < 100) {
			return $response->withStatus(400)->withHeader('Content-Type', 'application/json')
				->withBody((new StreamFactory())->createStream(json_encode(['error' => 'Invalid amount'])));
		}

		try {
			$stripe = $this->stripeClient;

			if ($frequency === 'one-time') {
				$intent = $stripe->paymentIntents->create([
					'amount' => $amountInCents,
					'currency' => 'usd',
				]);
				$clientSecret = $intent->client_secret;
				$paymentIntentId = $intent->id;
			} else {
				[$clientSecret, $paymentIntentId] = $this->createSubscriptionIntent($stripe, $amountInCents, $frequency, $email);
			}

			$this->createPendingOrder($amountInCents, $paymentIntentId);
		} catch (\Exception $e) {
			error_log('Error creating donate intent: ' . $e->getMessage());
			return $response->withStatus(500)->withHeader('Content-Type', 'application/json')
				->withBody((new StreamFactory())->createStream(json_encode(['error' => 'Failed to create intent'])));
		}

		$response->getBody()->write(json_encode(['clientSecret' => $clientSecret]));
		return $response->withHeader('Content-Type', 'application/json');
	}

	private function createPendingOrder(int $amountInCents, string $paymentIntentId): void
	{
		$amount = $amountInCents / 100;

		$order = new Order(
			orderId: null,
			patronId: null,
			subscriptionId: null,
			tax: 0.0,
			fee: 0.0,
			discount: 0.0,
			subtotal: $amount,
			total: $amount,
			status: OrderStatus::Pending,
			stripePaymentIntentId: $paymentIntentId,
		);

		$order->addItem(new OrderItem(
			itemId: null,
			orderId: null,
			itemType: ItemType::Donation->value,
			referenceId: null,
			quantity: 1,
			price: $amount,
		));

		$this->orderRepository->save($order);
	}

	/**
	 * @return array{0: string, 1: string} [clientSecret, paymentIntentId]
	 */
	private function createSubscriptionIntent(
		StripeClient $stripe,
		int $amountInCents,
		string $frequency,
		?string $email,
	): array {
		$customerParams = $email !== null ? ['email' => $email] : [];

		// Find or create the Stripe customer
		if ($email !== null) {
			$existing = $stripe->customers->search(['query' => "email:'$email'", 'limit' => 1]);
			$customer = $existing->data[0] ?? $stripe->customers->create($customerParams);
		} else {
			$customer = $stripe->customers->create($customerParams);
		}

		$interval = $frequency === 'monthly' ? 'month' : 'year';

		$price = $stripe->prices->create([
			'unit_amount' => $amountInCents,
			'currency' => 'usd',
			'recurring' => ['interval' => $interval],
			'product_data' => ['name' => 'Donation'],
		]);

		$subscription = $stripe->subscriptions->create([
			'customer' => $customer->id,
			'items' => [['price' => $price->id]],
			'payment_behavior' => 'default_incomplete',
			'payment_settings' => ['save_default_payment_method' => 'on_subscription'],
			'expand' => ['latest_invoice.payment_intent', 'latest_invoice.confirmation_secret'],
		]);

		$latestInvoice = $subscription->latest_invoice ?? null;
		$paymentIntent = is_object($latestInvoice) ? ($latestInvoice->payment_intent ?? null) : null;
		$confirmationSecret = is_object($latestInvoice) ? ($latestInvoice->confirmation_secret ?? null) : null;

		$clientSecret = null;
		$paymentIntentId = null;

		if (is_object($paymentIntent)) {
			$clientSecret = $paymentIntent->client_secret ?? null;
			$paymentIntentId = $paymentIntent->id ?? null;
		} elseif (is_string($paymentIntent)) {
			$expandedPaymentIntent = $stripe->paymentIntents->retrieve($paymentIntent);
			$clientSecret = $expandedPaymentIntent->client_secret ?? null;
			$paymentIntentId = $expandedPaymentIntent->id ?? null;
		}

		if ($clientSecret === null && is_object($confirmationSecret)) {
			$clientSecret = $confirmationSecret->client_secret ?? null;
		}

		if ($paymentIntentId === null && is_object($confirmationSecret)) {
			$confirmationPaymentIntent = $confirmationSecret->payment_intent ?? null;
			if (is_string($confirmationPaymentIntent)) {
				$paymentIntentId = $confirmationPaymentIntent;
			} elseif (is_object($confirmationPaymentIntent)) {
				$paymentIntentId = $confirmationPaymentIntent->id ?? null;
			}
		}

		if ($paymentIntentId === null && is_string($clientSecret) && preg_match('/^(pi_[^_]+)_secret_/', $clientSecret, $matches) === 1) {
			$paymentIntentId = $matches[1];
		}

		if (!is_string($clientSecret) || !is_string($paymentIntentId)) {
			throw new \RuntimeException('Unable to resolve subscription payment intent details');
		}

		return [$clientSecret, $paymentIntentId];
	}
}
