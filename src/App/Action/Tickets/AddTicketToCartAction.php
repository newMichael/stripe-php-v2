<?php

declare(strict_types=1);

namespace App\Action\Tickets;

use FORM\Ecommerce\Cart\Cart;
use FORM\Ecommerce\Cart\EventTicketCartItem;
use FORM\Ecommerce\Cart\SessionCartStorage;
use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class AddTicketToCartAction
{
	public function __construct(private readonly PDO $db) {}

	public function __invoke(Request $request, Response $response): Response
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
			ticketId: $ticketId,
			price: (float) $ticket['ticket_price'],
			quantity: $quantity,
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
