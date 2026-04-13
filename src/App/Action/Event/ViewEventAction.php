<?php

declare(strict_types=1);

namespace App\Action\Event;

use League\Plates\Engine;
use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;
use Slim\Flash\Messages;

final class ViewEventAction
{
	public function __construct(
		private readonly Engine $templates,
		private readonly Messages $flash,
		private readonly PDO $db
	) {}

	public function __invoke(Request $request, Response $response, array $args): Response
	{
		$eventSlug = $args['slug'] ?? '';
		$stmt = $this->db->prepare('SELECT * FROM events WHERE event_slug = :slug');
		$stmt->execute(['slug' => $eventSlug]);
		$event = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$event) {
			throw new HttpNotFoundException($request, 'Event not found');
		}

		$stmt = $this->db->prepare('SELECT * FROM event_tickets WHERE event_id = :event_id');
		$stmt->execute(['event_id' => $event['event_id']]);
		$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$response->getBody()->write($this->templates->render('events/detail', ['event' => $event, 'tickets' => $tickets]));
		return $response;
	}
}
