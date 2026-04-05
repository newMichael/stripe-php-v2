<?php

declare(strict_types=1);

$app = require __DIR__ . '/../bootstrap/app.php';
$container = $app->getContainer();

if ($container === null) {
	throw new RuntimeException('Container is not available.');
}

$driver = strtolower($_ENV['DB_CONNECTION'] ?? 'sqlite');
if ($driver !== 'sqlite') {
	throw new RuntimeException('This seed script only supports DB_CONNECTION=sqlite.');
}

$databasePath = $_ENV['DB_DATABASE'] ?? ($_ENV['DB_PATH'] ?? (__DIR__ . '/../storage/database.sqlite'));
if ($databasePath === '') {
	throw new RuntimeException('DB_DATABASE must point to a sqlite file path.');
}

$databaseDir = dirname($databasePath);
if ($databaseDir !== '' && $databaseDir !== '.' && !is_dir($databaseDir)) {
	if (!mkdir($databaseDir, 0775, true) && !is_dir($databaseDir)) {
		throw new RuntimeException('Failed to create sqlite directory: ' . $databaseDir);
	}
}

if (!file_exists($databasePath)) {
	if (!touch($databasePath)) {
		throw new RuntimeException('Failed to create sqlite database file: ' . $databasePath);
	}
}

/** @var PDO $pdo */
$pdo = $container->get(PDO::class);

$pdo->exec(
	'CREATE TABLE IF NOT EXISTS users (
		user_id INTEGER PRIMARY KEY,
		name TEXT NOT NULL,
		email TEXT NOT NULL UNIQUE,
		created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
	)'
);

$pdo->exec(
	'CREATE TABLE IF NOT EXISTS auth_tokens (
		id INTEGER PRIMARY KEY,
		user_id INTEGER NULL,
		purpose TEXT NOT NULL,
		token_hash TEXT NOT NULL,
		created_at TEXT NOT NULL,
		expires_at TEXT NOT NULL,
		consumed_at TEXT NULL,
		context_json TEXT NULL,
		FOREIGN KEY (user_id) REFERENCES users(user_id)
	)'
);

$count = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
if ($count === 0) {
	$insert = $pdo->prepare('INSERT INTO users (name, email) VALUES (:name, :email)');
	$insert->execute(['name' => 'Ada Lovelace', 'email' => 'ada@example.com']);
	$insert->execute(['name' => 'Grace Hopper', 'email' => 'grace@example.com']);
}

$tokenCount = (int) $pdo->query('SELECT COUNT(*) FROM auth_tokens')->fetchColumn();
if ($tokenCount === 0) {
	$insertToken = $pdo->prepare(
		'INSERT INTO auth_tokens (user_id, purpose, token_hash, created_at, expires_at, consumed_at, context_json)
		VALUES (:user_id, :purpose, :token_hash, :created_at, :expires_at, :consumed_at, :context_json)'
	);
	$insertToken->execute([
		'user_id' => 1,
		'purpose' => 'password_reset',
		'token_hash' => hash('sha256', 'seed-password-reset-token'),
		'created_at' => '2026-02-24 12:00:00',
		'expires_at' => '2026-02-24 13:00:00',
		'consumed_at' => null,
		'context_json' => json_encode(['ip' => '127.0.0.1', 'source' => 'seed']),
	]);
	$insertToken->execute([
		'user_id' => null,
		'purpose' => 'email_verification',
		'token_hash' => hash('sha256', 'seed-email-verification-token'),
		'created_at' => '2026-02-24 12:05:00',
		'expires_at' => '2026-02-24 14:05:00',
		'consumed_at' => null,
		'context_json' => json_encode(['campaign' => 'welcome']),
	]);
}

echo "SQLite users and auth_tokens tables are ready at {$databasePath}\n";
