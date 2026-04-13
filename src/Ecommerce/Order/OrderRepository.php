<?php

namespace FORM\Ecommerce\Order;

use PDO;

class OrderRepository
{
	public function __construct(private readonly PDO $pdo) {}

	public function findById(int $orderId): ?Order
	{
		$stmt = $this->pdo->prepare(
			'SELECT * FROM orders WHERE order_id = :order_id LIMIT 1'
		);
		$stmt->execute(['order_id' => $orderId]);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$row) {
			return null;
		}

		$order = Order::fromRow($row);
		$order->setItems($this->findItemsByOrderId($orderId));

		return $order;
	}

	/**
	 * Inserts the order row, then inserts each attached OrderItem.
	 * Returns the saved Order with order_id populated.
	 */
	public function save(Order $order): Order
	{
		$this->pdo->beginTransaction();

		try {
			$params = $order->toInsertParams();

			$this->pdo->prepare(
				'INSERT INTO orders
						(patron_id, subscription_id, order_tax, order_fee, order_discount,
							order_subtotal, order_total, order_status, stripe_payment_intent_id)
					VALUES
						(:patron_id, :subscription_id, :order_tax, :order_fee, :order_discount,
							:order_subtotal, :order_total, :order_status, :stripe_payment_intent_id)'
			)->execute($params);

			$orderId = (int) $this->pdo->lastInsertId();

			$saved = new Order(
				orderId: $orderId,
				patronId: $order->patronId,
				subscriptionId: $order->subscriptionId,
				tax: $order->tax,
				fee: $order->fee,
				discount: $order->discount,
				subtotal: $order->subtotal,
				total: $order->total,
				status: $order->status,
				stripePaymentIntentId: $order->stripePaymentIntentId,
			);

			$savedItems = [];
			foreach ($order->getItems() as $item) {
				$savedItems[] = $this->insertItem($item, $orderId);
			}
			$saved->setItems($savedItems);

			$this->pdo->commit();

			return $saved;
		} catch (\Throwable $e) {
			$this->pdo->rollBack();
			throw $e;
		}
	}

	public function updateStatus(int $orderId, OrderStatus $status): void
	{
		$this->pdo->prepare(
			'UPDATE orders SET order_status = :status WHERE order_id = :order_id'
		)->execute(['status' => $status->value, 'order_id' => $orderId]);
	}

	public function updatePaymentIntent(int $orderId, string $paymentIntentId): void
	{
		$this->pdo->prepare(
			'UPDATE orders SET stripe_payment_intent_id = :payment_intent_id WHERE order_id = :order_id'
		)->execute(['payment_intent_id' => $paymentIntentId, 'order_id' => $orderId]);
	}

	public function findByPaymentIntentId(string $paymentIntentId): ?Order
	{
		$stmt = $this->pdo->prepare(
			'SELECT * FROM orders WHERE stripe_payment_intent_id = :payment_intent_id LIMIT 1'
		);
		$stmt->execute(['payment_intent_id' => $paymentIntentId]);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$row) {
			return null;
		}

		$order = Order::fromRow($row);
		$order->setItems($this->findItemsByOrderId($order->orderId));

		return $order;
	}

	/** @return OrderItem[] */
	public function findItemsByOrderId(int $orderId): array
	{
		$stmt = $this->pdo->prepare(
			'SELECT * FROM order_items WHERE order_id = :order_id'
		);
		$stmt->execute(['order_id' => $orderId]);

		return array_map(
			fn(array $row) => OrderItem::fromRow($row),
			$stmt->fetchAll(PDO::FETCH_ASSOC)
		);
	}

	public function saveAddress(OrderAddress $address): void
	{
		$this->pdo->prepare(
			'INSERT INTO order_addresses
				(order_id, address_type, address_first_name, address_last_name, address_email,
				 address_line1, address_line2, address_city, address_state, address_postal_code, address_country)
			VALUES
				(:order_id, :address_type, :address_first_name, :address_last_name, :address_email,
				 :address_line1, :address_line2, :address_city, :address_state, :address_postal_code, :address_country)'
		)->execute([
			'order_id'           => $address->orderId,
			'address_type'       => $address->addressType->value,
			'address_first_name' => $address->addressFirstName,
			'address_last_name'  => $address->addressLastName,
			'address_email'      => $address->addressEmail,
			'address_line1'      => $address->addressLine1,
			'address_line2'      => $address->addressLine2,
			'address_city'       => $address->addressCity,
			'address_state'      => $address->addressState,
			'address_postal_code' => $address->addressPostalCode,
			'address_country'    => $address->addressCountry,
		]);
	}

	private function insertItem(OrderItem $item, int $orderId): OrderItem
	{
		$params = array_merge($item->toInsertParams(), ['order_id' => $orderId]);

		$this->pdo->prepare(
			'INSERT INTO order_items
				(order_id, item_type, reference_id, item_quantity, item_price, item_metadata)
			VALUES
				(:order_id, :item_type, :reference_id, :item_quantity, :item_price, :item_metadata)'
		)->execute($params);

		$itemId = (int) $this->pdo->lastInsertId();

		return new OrderItem(
			itemId: $itemId,
			orderId: $orderId,
			itemType: $item->itemType,
			referenceId: $item->referenceId,
			quantity: $item->quantity,
			price: $item->price,
			metadata: $item->metadata,
		);
	}
}
