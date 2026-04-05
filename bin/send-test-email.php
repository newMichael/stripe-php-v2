<?php

declare(strict_types=1);

use App\Mail\TestEmailSender;
use Slim\App;

require __DIR__ . '/../vendor/autoload.php';

/** @var App $app */
$app = require __DIR__ . '/../bootstrap/app.php';
$container = $app->getContainer();

if ($container === null) {
	fwrite(STDERR, "Container is not available.\n");
	exit(1);
}

$to = $argv[1] ?? null;
$subject = $argv[2] ?? null;

try {
	/** @var TestEmailSender $sender */
	$sender = $container->get(TestEmailSender::class);
	$result = $sender->send($to, $subject);

	echo "Email sent\n";
	echo sprintf("To: %s\n", $result['to']);
	echo sprintf("From: %s\n", $result['from']);
	echo sprintf("Subject: %s\n", $result['subject']);
	echo sprintf("Sent At: %s\n", $result['sent_at']);
	echo "Open Mailpit: https://slim-skelly.ddev.site:8026\n";
} catch (Throwable $e) {
	fwrite(STDERR, sprintf("Failed to send test email: %s\n", $e->getMessage()));
	exit(1);
}
