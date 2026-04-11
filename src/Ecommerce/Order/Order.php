<?php

namespace FORM\Ecommerce\Order;

class Order
{
	/** @var OrderItem[] */
	private array $items = [];

	public function __construct(
		public readonly ?int   $orderId,
		public readonly int    $patronId,
		public readonly ?int   $subscriptionId,
		public readonly float  $tax,
		public readonly float  $fee,
		public readonly float  $discount,
		public readonly float  $subtotal,
		public readonly float  $total,
		public readonly OrderStatus $status,
		public readonly ?string $orderDate = null,
	) {}

	public static function fromRow(array $row): self
	{
		return new self(
			orderId: (int) $row['order_id'],
			patronId: (int) $row['patron_id'],
			subscriptionId: isset($row['subscription_id']) ? (int) $row['subscription_id'] : null,
			tax: (float) $row['order_tax'],
			fee: (float) $row['order_fee'],
			discount: (float) $row['order_discount'],
			subtotal: (float) $row['order_subtotal'],
			total: (float) $row['order_total'],
			status: OrderStatus::from($row['order_status']),
			orderDate: $row['order_date'] ?? null,
		);
	}

	public function toInsertParams(): array
	{
		return [
			'patron_id'       => $this->patronId,
			'subscription_id' => $this->subscriptionId,
			'order_tax'       => $this->tax,
			'order_fee'       => $this->fee,
			'order_discount'  => $this->discount,
			'order_subtotal'  => $this->subtotal,
			'order_total'     => $this->total,
			'order_status'    => $this->status->value,
		];
	}

	/** @return OrderItem[] */
	public function getItems(): array
	{
		return $this->items;
	}

	public function setItems(array $items): void
	{
		$this->items = $items;
	}

	public function addItem(OrderItem $item): void
	{
		$this->items[] = $item;
	}
}
