<?php

declare(strict_types=1);

namespace App\Action\Tickets;

use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;

final class ViewTicketAction
{
	public function __construct(private readonly PDO $db) {}

	public function __invoke(Request $request, Response $response, array $args): Response
	{
		$ticketId = $args['id'] ?? '';
		$stmt = $this->db->prepare('SELECT * FROM event_tickets WHERE ticket_id = :id');
		$stmt->execute(['id' => $ticketId]);
		$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$ticket) {
			throw new HttpNotFoundException($request, 'Ticket not found');
		}

		$response->getBody()->write(json_encode($ticket));
		return $response->withHeader('Content-Type', 'application/json');
	}
}
