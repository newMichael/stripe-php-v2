<?php

declare(strict_types=1);

namespace App\Action\Donation;

use League\Plates\Engine;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Stripe\StripeClient;

final class DonationConfirmationAction
{
	public function __construct(
		private readonly Engine $templates,
		private readonly StripeClient $stripeClient,
	) {}

	public function __invoke(Request $request, Response $response): Response
	{
		$params = $request->getQueryParams();
		$paymentIntentId = $params['payment_intent'] ?? null;
		$redirectStatus = $params['redirect_status'] ?? null;

		if ($paymentIntentId === null || $redirectStatus !== 'succeeded') {
			$response->getBody()->write($this->templates->render('donate/confirmation', [
				'success' => false,
			]));
			return $response->withHeader('Content-Type', 'text/html');
		}

		$paymentIntent = $this->stripeClient->paymentIntents->retrieve($paymentIntentId, [
			'expand' => ['invoice.subscription'],
		]);

		$amountInCents = $paymentIntent->amount;
		$frequency = null;

		if ($paymentIntent->invoice !== null) {
			$interval = $paymentIntent->invoice->subscription?->items?->data[0]?->price?->recurring?->interval;
			$frequency = match ($interval) {
				'month' => 'monthly',
				'year'  => 'yearly',
				default => null,
			};
		}

		$response->getBody()->write($this->templates->render('donate/confirmation', [
			'success'       => true,
			'amountInCents' => $amountInCents,
			'frequency'     => $frequency,
		]));
		return $response->withHeader('Content-Type', 'text/html');
	}
}
