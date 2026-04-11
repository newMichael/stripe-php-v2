<?php

namespace FORM\Ecommerce\Order;

enum OrderStatus: string
{
	case Pending   = 'pending';
	case Complete  = 'complete';
	case Refunded  = 'refunded';
	case Cancelled = 'cancelled';
}
