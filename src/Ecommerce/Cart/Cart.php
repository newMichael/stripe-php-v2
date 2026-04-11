<?php

namespace FORM\Ecommerce\Cart;

class Cart
{
	/** @var array<string, CartItemInterface> keyed by CartItemInterface::key() */
	private array $items = [];

	public function __construct(private readonly CartStorageInterface $storage)
	{
		$this->items = $this->storage->load();
	}

	public function add(CartItemInterface $item): void
	{
		$key = $item->key();

		if (isset($this->items[$key])) {
			$existing = $this->items[$key];
			$this->items[$key] = $existing->withQuantity($existing->quantity() + $item->quantity());
		} else {
			$this->items[$key] = $item;
		}

		$this->storage->persist($this->items);
	}

	public function remove(string $key): void
	{
		unset($this->items[$key]);
		$this->storage->persist($this->items);
	}

	public function updateQuantity(string $key, int $quantity): void
	{
		if (!isset($this->items[$key])) {
			return;
		}

		if ($quantity <= 0) {
			$this->remove($key);
			return;
		}

		$this->items[$key] = $this->items[$key]->withQuantity($quantity);
		$this->storage->persist($this->items);
	}

	public function clear(): void
	{
		$this->items = [];
		$this->storage->clear();
	}

	/** @return CartItemInterface[] */
	public function getItems(): array
	{
		return array_values($this->items);
	}

	public function isEmpty(): bool
	{
		return empty($this->items);
	}

	public function subtotal(): float
	{
		return array_sum(array_map(fn(CartItemInterface $i) => $i->subtotal(), $this->items));
	}

	public function count(): int
	{
		return array_sum(array_map(fn(CartItemInterface $i) => $i->quantity(), $this->items));
	}
}
