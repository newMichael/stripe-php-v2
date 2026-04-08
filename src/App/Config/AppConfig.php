<?php

declare(strict_types=1);

namespace App\Config;

use Monolog\Level;
use Monolog\Logger;

final class AppConfig
{
	public function __construct(
		public readonly string $appName,
		public readonly bool $debug,
		public readonly string $logTarget,
		public readonly Level $logLevel,
		public readonly string $viewPath,
		public readonly string $mailerDsn,
		public readonly string $mailFrom,
		public readonly string $mailTestTo,
		public readonly DatabaseConfig $database
	) {
	}

	public static function fromEnvironment(array $env, string $projectRoot): self
	{
		return new self(
			appName: (string) ($env['APP_NAME'] ?? 'app'),
			debug: filter_var($env['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOL),
			logTarget: (string) ($env['LOG_TARGET'] ?? 'php://stderr'),
			logLevel: Logger::toMonologLevel($env['LOG_LEVEL'] ?? Level::Info->value),
			viewPath: $projectRoot . '/views',
			mailerDsn: (string) ($env['MAILER_DSN'] ?? 'smtp://127.0.0.1:1025'),
			mailFrom: (string) ($env['MAIL_FROM'] ?? 'slim-skelly@example.test'),
			mailTestTo: (string) ($env['MAIL_TEST_TO'] ?? 'mailpit@example.test'),
			database: DatabaseConfig::fromEnvironment($env, $projectRoot)
		);
	}
}
