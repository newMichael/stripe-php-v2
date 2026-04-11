<?php

declare(strict_types=1);

namespace App\Action;

use FORM\Ecommerce\Cart\Cart;
use FORM\Ecommerce\Cart\EventTicketCartItem;
use FORM\Ecommerce\Cart\SessionCartStorage;
use League\Plates\Engine;
use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class EventAction
{
	public function __construct(private readonly Engine $templates, private readonly PDO $db) {}

	public function __invoke(Request $request, Response $response): Response
	{
		$this->db->query('SELECT * FROM events');
		$events = $this->db->query('SELECT * FROM events')->fetchAll(PDO::FETCH_ASSOC);
		$response->getBody()->write($this->templates->render('events/overview', ['events' => $events]));
		return $response;
	}

	public function detailPage(Request $request, Response $response, array $args): Response
	{
		$eventSlug = $args['slug'] ?? '';
		$stmt = $this->db->prepare('SELECT * FROM events WHERE event_slug = :slug');
		$stmt->execute(['slug' => $eventSlug]);
		$event = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$event) {
			$response->getBody()->write('Event not found');
			return $response->withStatus(404);
		}

		$stmt = $this->db->prepare('SELECT * FROM event_tickets WHERE event_id = :event_id');
		$stmt->execute(['event_id' => $event['event_id']]);
		$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$response->getBody()->write($this->templates->render('events/detail', ['event' => $event, 'tickets' => $tickets]));
		return $response;
	}

	public function ticketDetails(Request $request, Response $response, array $args): Response
	{
		$ticketId = $args['id'] ?? '';
		$stmt = $this->db->prepare('SELECT * FROM event_tickets WHERE ticket_id = :id');
		$stmt->execute(['id' => $ticketId]);
		$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$ticket) {
			$response->getBody()->write('Ticket not found');
			return $response->withStatus(404);
		}

		$response->getBody()->write(json_encode($ticket));
		return $response->withHeader('Content-Type', 'application/json');
	}

	public function addToCart(Request $request, Response $response): Response
	{
		$body = (array) $request->getParsedBody();

		$ticketId = isset($body['ticket_id']) ? (int) $body['ticket_id'] : 0;
		$quantity  = isset($body['quantity'])  ? (int) $body['quantity']  : 0;
		$attendees = isset($body['attendee']) && is_array($body['attendee']) ? $body['attendee'] : [];

		if ($ticketId <= 0 || $quantity <= 0) {
			return $this->jsonError($response, 'Invalid ticket or quantity.', 422);
		}

		$stmt = $this->db->prepare('SELECT * FROM event_tickets WHERE ticket_id = :id');
		$stmt->execute(['id' => $ticketId]);
		$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$ticket) {
			return $this->jsonError($response, 'Ticket not found.', 404);
		}

		if ($ticket['ticket_quantity'] < $quantity) {
			return $this->jsonError($response, 'Not enough tickets available.', 409);
		}

		$sanitizedAttendees = [];
		foreach ($attendees as $attendee) {
			if (!is_array($attendee)) {
				continue;
			}
			$sanitizedAttendees[] = [
				'name'  => trim((string) ($attendee['name'] ?? '')),
				'email' => trim((string) ($attendee['email'] ?? '')),
			];
		}

		$item = new EventTicketCartItem(
			ticketId:  $ticketId,
			price:     (float) $ticket['ticket_price'],
			quantity:  $quantity,
			attendees: $sanitizedAttendees,
		);

		$cart = new Cart(new SessionCartStorage());
		$cart->add($item);

		$response->getBody()->write(json_encode(['success' => true, 'cartCount' => $cart->count()]));
		return $response->withHeader('Content-Type', 'application/json');
	}

	private function jsonError(Response $response, string $message, int $status): Response
	{
		$response->getBody()->write(json_encode(['success' => false, 'error' => $message]));
		return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
	}
}
