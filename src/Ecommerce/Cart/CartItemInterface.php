<?php

namespace FORM\Ecommerce\Cart;

interface CartItemInterface
{
	public function itemType(): ItemType;

	/**
	 * Stable identifier for this line in the cart.
	 * Same type + same reference = same line (quantities are merged).
	 */
	public function key(): string;

	public function quantity(): int;
	public function price(): float;
	public function subtotal(): float;

	/** Return a new instance with the updated quantity. */
	public function withQuantity(int $quantity): static;

	/** Serialize to a plain array for session storage. */
	public function toArray(): array;
}
