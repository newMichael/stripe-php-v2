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
	'CREATE TABLE IF NOT EXISTS events (
		event_id INT AUTO_INCREMENT PRIMARY KEY,
		event_title VARCHAR(100) NOT NULL,
		event_slug VARCHAR(100) NOT NULL UNIQUE,
		event_start DATETIME NOT NULL,
		event_end DATETIME NOT NULL,
		event_status TINYINT NOT NULL
	) ENGINE=InnoDB'
);

$pdo->exec(
	'CREATE TABLE IF NOT EXISTS event_tickets (
		ticket_id INT AUTO_INCREMENT PRIMARY KEY,
		event_id INT NOT NULL,
		ticket_title VARCHAR(100) NOT NULL,
		ticket_price DECIMAL(10, 2) NOT NULL,
		ticket_quantity INT NOT NULL,
		ticket_status TINYINT NOT NULL
	) ENGINE=InnoDB'
);

$pdo->exec(
	'CREATE TABLE IF NOT EXISTS orders (
		order_id INT AUTO_INCREMENT PRIMARY KEY,
		patron_id INT NULL,
		subscription_id INT NULL,
		order_tax DECIMAL(10, 2) NOT NULL,
		order_fee DECIMAL(10, 2) NOT NULL,
		order_discount DECIMAL(10, 2) NOT NULL,
		order_subtotal DECIMAL(10, 2) NOT NULL,
		order_total DECIMAL(10, 2) NOT NULL,
		order_status VARCHAR(50) NOT NULL,
		stripe_payment_intent_id VARCHAR(255) NULL,
		order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
	) ENGINE=InnoDB'
);

$pdo->exec(
	'CREATE TABLE IF NOT EXISTS order_items (
		item_id INT AUTO_INCREMENT PRIMARY KEY,
		order_id INT NOT NULL,
		item_type VARCHAR(50) NOT NULL,
		reference_id INT NULL,
		item_quantity INT NOT NULL,
		item_price DECIMAL(10, 2) NOT NULL,
		item_metadata JSON NULL,
		FOREIGN KEY (order_id) REFERENCES orders(order_id)
	) ENGINE=InnoDB'
);

$pdo->exec(
	'CREATE TABLE IF NOT EXISTS order_payments (
		payment_id INT AUTO_INCREMENT PRIMARY KEY,
		stripe_payment_method_id VARCHAR(255) NOT NULL,
		order_id INT NOT NULL,
		payment_amount DECIMAL(10, 2) NOT NULL,
		payment_status VARCHAR(50) NOT NULL,
		payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
		FOREIGN KEY (order_id) REFERENCES orders(order_id)
	) ENGINE=InnoDB'
);

$pdo->exec(
	'CREATE TABLE IF NOT EXISTS patrons (
		patron_id INT AUTO_INCREMENT PRIMARY KEY,
		patron_fname VARCHAR(50) NOT NULL,
		patron_lname VARCHAR(50) NOT NULL,
		patron_email VARCHAR(100) NOT NULL UNIQUE,
		patron_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
		patron_status TINYINT NOT NULL
	) ENGINE=InnoDB'
);

$pdo->exec(
	'CREATE TABLE IF NOT EXISTS subscriptions (
		subscription_id INT AUTO_INCREMENT PRIMARY KEY,
		stripe_subscription_id VARCHAR(255) NOT NULL,
		stripe_customer_id VARCHAR(255) NOT NULL,
		order_id INT NOT NULL,
		patron_id INT NOT NULL,
		subscription_title VARCHAR(100) NOT NULL,
		subscription_amount DECIMAL(10, 2) NOT NULL,
		subscription_status VARCHAR(50) NOT NULL,
		subscription_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
		subscription_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		subscription_cancelled_at TIMESTAMP NULL,
		FOREIGN KEY (order_id) REFERENCES orders(order_id),
		FOREIGN KEY (patron_id) REFERENCES patrons(patron_id)
	) ENGINE=InnoDB'
);

$pdo->exec(
	'CREATE TABLE IF NOT EXISTS order_addresses (
		address_id INT AUTO_INCREMENT PRIMARY KEY,
		order_id INT NOT NULL,
		address_type ENUM(\'billing\', \'shipping\') NOT NULL,
		address_first_name VARCHAR(50) NULL,
		address_last_name VARCHAR(50) NULL,
		address_email VARCHAR(100) NULL,
		address_line1 VARCHAR(255) NULL,
		address_line2 VARCHAR(255) NULL,
		address_city VARCHAR(100) NULL,
		address_state VARCHAR(100) NULL,
		address_postal_code VARCHAR(20) NULL,
		address_country VARCHAR(2) NULL,
		FOREIGN KEY (order_id) REFERENCES orders(order_id)
	) ENGINE=InnoDB'
);

$pdo->exec(
	'CREATE TABLE IF NOT EXISTS membership_levels (
		membership_id INT AUTO_INCREMENT PRIMARY KEY,
		membership_title VARCHAR(100) NOT NULL,
		membership_price DECIMAL(10, 2) NOT NULL
	) ENGINE=InnoDB'
);

echo "Tables created successfully.\n";
