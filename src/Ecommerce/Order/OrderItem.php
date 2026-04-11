<?php

namespace FORM\Ecommerce\Order;

class OrderItem
{
	public function __construct(
		public readonly ?int   $itemId,
		public readonly ?int   $orderId,
		public readonly string $itemType,
		public readonly ?int   $referenceId,
		public readonly int    $quantity,
		public readonly float  $price,
		public readonly ?array $metadata = null,
	) {}

	public static function fromRow(array $row): self
	{
		return new self(
			itemId: (int) $row['item_id'],
			orderId: (int) $row['order_id'],
			itemType: $row['item_type'],
			referenceId: isset($row['reference_id']) ? (int) $row['reference_id'] : null,
			quantity: (int) $row['item_quantity'],
			price: (float) $row['item_price'],
			metadata: isset($row['item_metadata']) ? json_decode($row['item_metadata'], true) : null,
		);
	}

	public function toInsertParams(): array
	{
		return [
			'order_id'      => $this->orderId,
			'item_type'     => $this->itemType,
			'reference_id'  => $this->referenceId,
			'item_quantity' => $this->quantity,
			'item_price'    => $this->price,
			'item_metadata' => $this->metadata !== null ? json_encode($this->metadata) : null,
		];
	}
}
