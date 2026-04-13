<?php

declare(strict_types=1);

namespace FORM\Ecommerce\Order;

enum OrderAddressType: string
{
	case Billing = 'billing';
	case Shipping = 'shipping';
}
