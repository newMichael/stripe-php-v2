<?php

namespace FORM\Ecommerce\Cart;

interface CartStorageInterface
{
	public function load(): array;
	public function persist(array $items): void;
	public function clear(): void;
}
