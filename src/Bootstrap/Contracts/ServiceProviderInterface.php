<?php

declare(strict_types=1);

namespace App\Bootstrap\Contracts;

use App\Config\AppConfig;
use League\Container\Container;

interface ServiceProviderInterface
{
	public function register(Container $container, AppConfig $config): void;
}
