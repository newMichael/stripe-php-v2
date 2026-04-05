<?php

declare(strict_types=1);

namespace App\Bootstrap\Contracts;

use App\Config\AppConfig;
use Slim\App;

interface MiddlewareProviderInterface
{
	public function register(App $app, AppConfig $config): void;
}
