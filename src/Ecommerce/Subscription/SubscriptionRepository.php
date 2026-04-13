<?php

declare(strict_types=1);

namespace FORM\Ecommerce\Subscription;

use PDO;

class SubscriptionRepository
{
	public function __construct(private readonly PDO $pdo) {}

	public function findByStripeSubscriptionId(string $stripeSubscriptionId): ?Subscription
	{
		$stmt = $this->pdo->prepare(
			'SELECT * FROM subscriptions WHERE stripe_subscription_id = :id LIMIT 1'
		);
		$stmt->execute(['id' => $stripeSubscriptionId]);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		return $row ? Subscription::fromRow($row) : null;
	}

	public function save(Subscription $subscription): Subscription
	{
		$this->pdo->prepare(
			'INSERT INTO subscriptions
				(stripe_subscription_id, stripe_customer_id, order_id, patron_id,
				 subscription_title, subscription_amount, subscription_status)
			VALUES
				(:stripe_subscription_id, :stripe_customer_id, :order_id, :patron_id,
				 :title, :amount, :status)'
		)->execute([
			'stripe_subscription_id' => $subscription->stripeSubscriptionId,
			'stripe_customer_id'     => $subscription->stripeCustomerId,
			'order_id'               => $subscription->orderId,
			'patron_id'              => $subscription->patronId,
			'title'                  => $subscription->title,
			'amount'                 => $subscription->amount,
			'status'                 => $subscription->status,
		]);

		return new Subscription(
			subscriptionId:       (int) $this->pdo->lastInsertId(),
			stripeSubscriptionId: $subscription->stripeSubscriptionId,
			stripeCustomerId:     $subscription->stripeCustomerId,
			orderId:              $subscription->orderId,
			patronId:             $subscription->patronId,
			title:                $subscription->title,
			amount:               $subscription->amount,
			status:               $subscription->status,
		);
	}

	public function updateStatus(int $subscriptionId, string $status): void
	{
		$params = ['status' => $status, 'id' => $subscriptionId];

		if ($status === 'cancelled') {
			$this->pdo->prepare(
				'UPDATE subscriptions
				SET subscription_status = :status, subscription_cancelled_at = CURRENT_TIMESTAMP
				WHERE subscription_id = :id'
			)->execute($params);
		} else {
			$this->pdo->prepare(
				'UPDATE subscriptions SET subscription_status = :status WHERE subscription_id = :id'
			)->execute($params);
		}
	}
}
