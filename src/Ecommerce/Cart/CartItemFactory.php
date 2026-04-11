<?php

namespace FORM\Ecommerce\Cart;

class CartItemFactory
{
	public function fromArray(array $data): CartItemInterface
	{
		return match (ItemType::from($data['item_type'])) {
			ItemType::EventTicket => EventTicketCartItem::fromArray($data),
			ItemType::Donation    => throw new \LogicException('DonationCartItem not yet implemented.'),
			ItemType::Membership  => throw new \LogicException('MembershipCartItem not yet implemented.'),
		};
	}
}
