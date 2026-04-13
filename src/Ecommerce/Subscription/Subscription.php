<?php

declare(strict_types=1);

namespace FORM\Ecommerce\Subscription;

class Subscription
{
	public function __construct(
		public readonly ?int    $subscriptionId,
		public readonly string  $stripeSubscriptionId,
		public readonly string  $stripeCustomerId,
		public readonly int     $orderId,
		public readonly int     $patronId,
		public readonly string  $title,
		public readonly float   $amount,
		public readonly string  $status,
		public readonly ?string $created = null,
		public readonly ?string $cancelledAt = null,
	) {}

	public static function fromRow(array $row): self
	{
		return new self(
			subscriptionId:       (int) $row['subscription_id'],
			stripeSubscriptionId: $row['stripe_subscription_id'],
			stripeCustomerId:     $row['stripe_customer_id'],
			orderId:              (int) $row['order_id'],
			patronId:             (int) $row['patron_id'],
			title:                $row['subscription_title'],
			amount:               (float) $row['subscription_amount'],
			status:               $row['subscription_status'],
			created:              $row['subscription_created'] ?? null,
			cancelledAt:          $row['subscription_cancelled_at'] ?? null,
		);
	}
}
