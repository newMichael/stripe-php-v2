<?php

namespace FORM\Ecommerce\Cart;

class EventTicketCartItem implements CartItemInterface
{
	public function __construct(
		private readonly int   $ticketId,
		private readonly float $price,
		private readonly int   $quantity,
		private readonly array $attendees = [],
	) {}

	public static function fromArray(array $data): self
	{
		return new self(
			ticketId: (int)   $data['ticket_id'],
			price: (float) $data['price'],
			quantity: (int)   $data['quantity'],
			attendees: $data['attendees'] ?? [],
		);
	}

	public function itemType(): ItemType
	{
		return ItemType::EventTicket;
	}
	public function key(): string
	{
		return 'event_ticket:' . $this->ticketId;
	}
	public function ticketId(): int
	{
		return $this->ticketId;
	}
	public function quantity(): int
	{
		return $this->quantity;
	}
	public function price(): float
	{
		return $this->price;
	}
	public function subtotal(): float
	{
		return $this->price * $this->quantity;
	}

	/** @return array<array{name: string, email: string}> */
	public function attendees(): array
	{
		return $this->attendees;
	}

	public function withQuantity(int $quantity): static
	{
		return new self($this->ticketId, $this->price, $quantity, $this->attendees);
	}

	public function toArray(): array
	{
		return [
			'item_type' => ItemType::EventTicket->value,
			'ticket_id' => $this->ticketId,
			'price'     => $this->price,
			'quantity'  => $this->quantity,
			'attendees' => $this->attendees,
		];
	}
}
