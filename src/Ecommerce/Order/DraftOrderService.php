<?php

declare(strict_types=1);

namespace FORM\Ecommerce\Order;

use FORM\Ecommerce\Cart\Cart;
use FORM\Ecommerce\Cart\CartItemInterface;
use FORM\Ecommerce\Cart\EventTicketCartItem;

class DraftOrderService
{
	public function __construct(private readonly OrderRepository $orderRepository) {}

	public function createFromCart(Cart $cart): Order
	{
		$subtotal = $cart->subtotal();

		$order = new Order(
			orderId: null,
			patronId: null,
			subscriptionId: null,
			tax: 0.0,
			fee: 0.0,
			discount: 0.0,
			subtotal: $subtotal,
			total: $subtotal,
			status: OrderStatus::Pending,
		);

		foreach ($cart->getItems() as $cartItem) {
			$order->addItem($this->cartItemToOrderItem($cartItem));
		}

		return $this->orderRepository->save($order);
	}

	private function cartItemToOrderItem(CartItemInterface $cartItem): OrderItem
	{
		$referenceId = null;
		$metadata = null;

		if ($cartItem instanceof EventTicketCartItem) {
			$referenceId = $cartItem->ticketId();
			$attendees = $cartItem->attendees();
			if (!empty($attendees)) {
				$metadata = ['attendees' => $attendees];
			}
		}

		return new OrderItem(
			itemId: null,
			orderId: null,
			itemType: $cartItem->itemType()->value,
			referenceId: $referenceId,
			quantity: $cartItem->quantity(),
			price: $cartItem->price(),
			metadata: $metadata,
		);
	}
}
