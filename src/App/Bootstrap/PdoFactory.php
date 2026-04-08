<?php

declare(strict_types=1);

namespace App\Bootstrap;

use App\Config\DatabaseConfig;
use PDO;
use PDOException;
use RuntimeException;

final class PdoFactory
{
	public function create(DatabaseConfig $databaseConfig): PDO
	{
		$options = [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		];

		if ($databaseConfig->isSqlite()) {
			$this->prepareSqliteStorage($databaseConfig->databasePath);

			try {
				return new PDO('sqlite:' . $databaseConfig->databasePath, null, null, $options);
			} catch (PDOException $e) {
				throw new RuntimeException(
					'Failed to create SQLite PDO connection at "' . $databaseConfig->databasePath . '": ' . $e->getMessage(),
					0,
					$e
				);
			}
		}

		$dsn = sprintf(
			'mysql:host=%s;port=%s;dbname=%s;charset=%s',
			$databaseConfig->host,
			$databaseConfig->port,
			$databaseConfig->database,
			$databaseConfig->charset
		);

		$options[PDO::ATTR_EMULATE_PREPARES] = false;

		try {
			return new PDO($dsn, $databaseConfig->username, $databaseConfig->password, $options);
		} catch (PDOException $e) {
			throw new RuntimeException('Failed to create MySQL/MariaDB PDO connection.', 0, $e);
		}
	}

	private function prepareSqliteStorage(string $databasePath): void
	{
		if (!extension_loaded('pdo_sqlite')) {
			throw new RuntimeException(
				'SQLite requires the pdo_sqlite PHP extension, but it is not loaded in this runtime.'
			);
		}

		if ($databasePath === ':memory:') {
			return;
		}

		$directory = dirname($databasePath);
		if ($directory !== '' && $directory !== '.' && !is_dir($directory)) {
			if (!mkdir($directory, 0775, true) && !is_dir($directory)) {
				throw new RuntimeException('Unable to create SQLite directory: ' . $directory);
			}
		}

		if (!file_exists($databasePath) && !touch($databasePath)) {
			throw new RuntimeException('Unable to create SQLite database file: ' . $databasePath);
		}

		if (!is_readable($databasePath) || !is_writable($databasePath)) {
			throw new RuntimeException(
				'SQLite database path is not readable/writable: ' . $databasePath
			);
		}
	}
}
