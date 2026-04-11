<?php

namespace FORM\Ecommerce\Cart;

enum ItemType: string
{
	case EventTicket = 'event_ticket';
	case Donation    = 'donation';
	case Membership  = 'membership';
}
