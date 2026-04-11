<?php

namespace FORM\Ecommerce\Cart;

enum Frequency: string
{
	case OneTime   = 'one-time';
	case Recurring = 'recurring';
}
