<?php

$app = require __DIR__ . '/../../bootstrap/app.php';
$container = $app->getContainer();

if ($container === null) {
	throw new RuntimeException('Container is not available.');
}

$driver = strtolower($_ENV['DB_CONNECTION'] ?? '');
if ($driver !== 'mariadb') {
	throw new RuntimeException('This seed script only supports DB_CONNECTION=mariadb.');
}

/** @var PDO $pdo */
$pdo = $container->get(PDO::class);

require_once __DIR__ . '/create-tables.php';
require_once __DIR__ . '/seed-tables.php';
