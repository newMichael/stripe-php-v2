<?php

declare(strict_types=1);

namespace App\Action;

use App\Mail\TestEmailSender;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Throwable;

final class SendTestEmailAction
{
	public function __construct(private readonly TestEmailSender $sender)
	{
	}

	public function __invoke(Request $request, Response $response): Response
	{
		$query = $request->getQueryParams();
		$to = isset($query['to']) ? (string) $query['to'] : null;
		$subject = isset($query['subject']) ? (string) $query['subject'] : null;

		try {
			$result = $this->sender->send($to, $subject);

			$response->getBody()->write((string) json_encode([
				'ok' => true,
				'email' => $result,
			], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

			return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
		} catch (Throwable $e) {
			$response = $response->withStatus(500);
			$response->getBody()->write((string) json_encode([
				'ok' => false,
				'error' => $e->getMessage(),
			], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

			return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
		}
	}
}
