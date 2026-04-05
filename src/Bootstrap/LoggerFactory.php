<?php

declare(strict_types=1);

namespace App\Bootstrap;

use App\Config\AppConfig;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

final class LoggerFactory
{
	public function create(AppConfig $config): Logger
	{
		$logger = new Logger($config->appName);
		$logger->pushHandler(new StreamHandler($config->logTarget, $config->logLevel));

		return $logger;
	}
}
