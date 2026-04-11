<?php

namespace FORM\Ecommerce\Cart;

class SessionCartStorage implements CartStorageInterface
{
	private const SESSION_KEY = 'ecommerce_cart';

	public function __construct(private readonly CartItemFactory $factory = new CartItemFactory())
	{
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}
	}

	/** @return array<string, CartItemInterface> */
	public function load(): array
	{
		$raw = $_SESSION[self::SESSION_KEY] ?? [];
		$items = [];

		foreach ($raw as $key => $data) {
			$items[$key] = $this->factory->fromArray($data);
		}

		return $items;
	}

	/** @param array<string, CartItemInterface> $items */
	public function persist(array $items): void
	{
		$_SESSION[self::SESSION_KEY] = array_map(
			fn(CartItemInterface $item) => $item->toArray(),
			$items,
		);
	}

	public function clear(): void
	{
		unset($_SESSION[self::SESSION_KEY]);
	}
}
