<?php

declare(strict_types=1);

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

$pdo->exec(
	'INSERT INTO events (event_title, event_slug, event_start, event_end, event_status) VALUES
	("Spring Gala", "spring-gala", "2024-05-01 19:00:00", "2024-05-01 23:00:00", 1),
	("Summer Festival", "summer-festival", "2024-06-15 12:00:00", "2024-06-15 22:00:00", 1),
	("Autumn Concert", "autumn-concert", "2024-09-20 18:00:00", "2024-09-20 21:00:00", 1)'
);

$pdo->exec(
	'INSERT INTO event_tickets (event_id, ticket_title, ticket_price, ticket_quantity, ticket_status) VALUES
	(1, "General Admission", 50.00, 100, 1),
	(1, "VIP Pass", 150.00, 20, 1),
	(1, "Free Entry", 0.00, 50, 1),
	(2, "Early Bird", 30.00, 200, 1),
	(2, "Regular", 40.00, 300, 1),
	(3, "Standard", 25.00, 150, 1),
	(3, "Premium", 75.00, 50, 1)'
);
