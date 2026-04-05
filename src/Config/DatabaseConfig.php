<?php

declare(strict_types=1);

namespace App\Config;

use RuntimeException;

final class DatabaseConfig
{
	public function __construct(
		public readonly string $driver,
		public readonly string $databasePath,
		public readonly string $host,
		public readonly string $port,
		public readonly string $database,
		public readonly string $username,
		public readonly string $password,
		public readonly string $charset
	) {
	}

	public static function fromEnvironment(array $env, string $projectRoot): self
	{
		$driver = strtolower((string) ($env['DB_CONNECTION'] ?? 'sqlite'));
		if ($driver === 'sqlite') {
			$databasePath = $env['DB_DATABASE'] ?? ($env['DB_PATH'] ?? 'storage/database.sqlite');
			if ($databasePath === '' || $databasePath === false) {
				throw new RuntimeException('DB_DATABASE must be set when DB_CONNECTION=sqlite.');
			}

			return new self(
				driver: 'sqlite',
				databasePath: self::resolveSqlitePath((string) $databasePath, $projectRoot),
				host: '',
				port: '',
				database: '',
				username: '',
				password: '',
				charset: ''
			);
		}

		if ($driver !== 'mysql' && $driver !== 'mariadb') {
			throw new RuntimeException('Unsupported DB_CONNECTION. Use mysql, mariadb, or sqlite.');
		}

		$database = (string) ($env['DB_DATABASE'] ?? '');
		if ($database === '') {
			throw new RuntimeException('DB_DATABASE must be set when DB_CONNECTION=mysql or DB_CONNECTION=mariadb.');
		}

		return new self(
			driver: $driver,
			databasePath: '',
			host: (string) ($env['DB_HOST'] ?? '127.0.0.1'),
			port: (string) ($env['DB_PORT'] ?? '3306'),
			database: $database,
			username: (string) ($env['DB_USERNAME'] ?? ''),
			password: (string) ($env['DB_PASSWORD'] ?? ''),
			charset: (string) ($env['DB_CHARSET'] ?? 'utf8mb4')
		);
	}

	public function isSqlite(): bool
	{
		return $this->driver === 'sqlite';
	}

	private static function resolveSqlitePath(string $databasePath, string $projectRoot): string
	{
		if ($databasePath === ':memory:') {
			return $databasePath;
		}

		if (self::isAbsolutePath($databasePath)) {
			return $databasePath;
		}

		return rtrim($projectRoot, '/\\') . DIRECTORY_SEPARATOR . ltrim($databasePath, '/\\');
	}

	private static function isAbsolutePath(string $path): bool
	{
		if ($path === '') {
			return false;
		}

		return str_starts_with($path, '/') || preg_match('/^[A-Za-z]:[\\\\\\/]/', $path) === 1;
	}
}
