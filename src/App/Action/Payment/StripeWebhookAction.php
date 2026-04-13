<?php

declare(strict_types=1);

namespace App\Action\Payment;

use FORM\Ecommerce\Order\Order;
use FORM\Ecommerce\Order\OrderAddress;
use FORM\Ecommerce\Order\OrderAddressType;
use FORM\Ecommerce\Order\OrderRepository;
use FORM\Ecommerce\Order\OrderStatus;
use FORM\Ecommerce\Patron\Patron;
use FORM\Ecommerce\Patron\PatronRepository;
use FORM\Ecommerce\Subscription\Subscription;
use FORM\Ecommerce\Subscription\SubscriptionRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Stripe\StripeClient;

final class StripeWebhookAction
{
	public function __construct(
		private readonly OrderRepository $orderRepository,
		private readonly PatronRepository $patronRepository,
		private readonly SubscriptionRepository $subscriptionRepository,
		private readonly StripeClient $stripeClient,
	) {}

	public function __invoke(Request $request, Response $response): Response
	{
		$payload = (string) $request->getBody();
		$sigHeader = $request->getHeaderLine('Stripe-Signature');
		$secret = $_ENV['STRIPE_WEBHOOK_SECRET'];

		try {
			$event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $secret);
		} catch (\Stripe\Exception\SignatureVerificationException $e) {
			return $response->withStatus(400);
		}

		switch ($event->type) {
			case 'payment_intent.succeeded':
				$this->handlePaymentIntentSucceeded($event->data->object);
				break;

			case 'invoice.payment_succeeded':
				$this->handleInvoicePaymentSucceeded($event->data->object);
				break;

			case 'customer.subscription.deleted':
				$this->handleSubscriptionDeleted($event->data->object);
				break;

			default:
				break;
		}

		$response->getBody()->write(json_encode(['received' => true]));
		return $response->withHeader('Content-Type', 'application/json');
	}

	private function handlePaymentIntentSucceeded(object $paymentIntent): void
	{
		// Skip subscription invoices — handled by invoice.payment_succeeded
		if ($paymentIntent->invoice !== null) {
			return;
		}

		$order = $this->orderRepository->findByPaymentIntentId($paymentIntent->id);

		if ($order !== null) {
			$this->orderRepository->updateStatus($order->orderId, OrderStatus::Complete);

			$charge = $this->stripeClient->charges->retrieve($paymentIntent->latest_charge);
			$billing = $charge->billing_details;
			$nameParts = explode(' ', $billing->name ?? '', 2);

			$this->orderRepository->saveAddress(new OrderAddress(
				addressId: null,
				orderId: $order->orderId,
				addressType: OrderAddressType::Billing,
				addressFirstName: $nameParts[0] !== '' ? $nameParts[0] : null,
				addressLastName: $nameParts[1] ?? null,
				addressEmail: $billing->email,
				addressLine1: $billing->address?->line1,
				addressLine2: $billing->address?->line2,
				addressCity: $billing->address?->city,
				addressState: $billing->address?->state,
				addressPostalCode: $billing->address?->postal_code,
				addressCountry: $billing->address?->country,
			));
		}
	}

	private function handleInvoicePaymentSucceeded(object $invoice): void
	{
		// Only handle subscription invoices
		if ($invoice->subscription === null) {
			return;
		}

		$stripeSubscriptionId = is_string($invoice->subscription)
			? $invoice->subscription
			: $invoice->subscription->id;

		$existing = $this->subscriptionRepository->findByStripeSubscriptionId($stripeSubscriptionId);

		if ($existing !== null) {
			// Renewal — ensure status is active
			$this->subscriptionRepository->updateStatus($existing->subscriptionId, 'active');
			return;
		}

		// First invoice — create patron, order, and subscription records
		$stripeSubscription = $this->stripeClient->subscriptions->retrieve($stripeSubscriptionId, [
			'expand' => ['items.data.price', 'customer'],
		]);

		$customer = $stripeSubscription->customer;
		$email = $customer->email ?? '';
		$name = $customer->name ?? '';
		$nameParts = explode(' ', trim($name), 2);
		$firstName = $nameParts[0] !== '' ? $nameParts[0] : 'Donor';
		$lastName = $nameParts[1] ?? '';

		$patron = $this->patronRepository->findByEmail($email)
			?? $this->patronRepository->save(new Patron(
				patronId: null,
				firstName: $firstName,
				lastName: $lastName,
				email: $email,
			));

		$priceItem = $stripeSubscription->items->data[0] ?? null;
		$amountInCents = $priceItem?->price?->unit_amount ?? (int) $invoice->amount_paid;
		$interval = $priceItem?->price?->recurring?->interval ?? 'month';
		$title = $interval === 'month' ? 'Monthly Donation' : 'Yearly Donation';

		$order = $this->orderRepository->save(new Order(
			orderId: null,
			patronId: $patron->patronId,
			subscriptionId: null,
			tax: 0,
			fee: 0,
			discount: 0,
			subtotal: $amountInCents / 100,
			total: $amountInCents / 100,
			status: OrderStatus::Complete,
			stripePaymentIntentId: is_string($invoice->payment_intent)
				? $invoice->payment_intent
				: $invoice->payment_intent?->id,
		));

		$this->subscriptionRepository->save(new Subscription(
			subscriptionId: null,
			stripeSubscriptionId: $stripeSubscriptionId,
			stripeCustomerId: $customer->id,
			orderId: $order->orderId,
			patronId: $patron->patronId,
			title: $title,
			amount: $amountInCents / 100,
			status: 'active',
		));
	}

	private function handleSubscriptionDeleted(object $stripeSubscription): void
	{
		$existing = $this->subscriptionRepository->findByStripeSubscriptionId($stripeSubscription->id);

		if ($existing !== null) {
			$this->subscriptionRepository->updateStatus($existing->subscriptionId, 'cancelled');
		}
	}
}
