<?php

declare(strict_types=1);

namespace App\Action\Cart;

use FORM\Ecommerce\Cart\Cart;
use FORM\Ecommerce\Cart\EventTicketCartItem;
use FORM\Ecommerce\Cart\SessionCartStorage;
use League\Plates\Engine;
use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class ViewCartAction
{
	public function __construct(private readonly Engine $templates, private readonly PDO $db) {}

	public function __invoke(Request $request, Response $response): Response
	{
		$cart = new Cart(new SessionCartStorage());
		$items = $cart->getItems();

		$enriched = [];
		foreach ($items as $item) {
			if ($item instanceof EventTicketCartItem) {
				$stmt = $this->db->prepare(
					'SELECT t.ticket_title, e.event_title
					FROM event_tickets t
					JOIN events e ON e.event_id = t.event_id
					WHERE t.ticket_id = :id'
				);
				$stmt->execute(['id' => $item->ticketId()]);
				$row = $stmt->fetch(PDO::FETCH_ASSOC);

				$enriched[] = [
					'key'       => $item->key(),
					'label'     => $row ? "{$row['ticket_title']} — {$row['event_title']}" : "Ticket #{$item->ticketId()}",
					'quantity'  => $item->quantity(),
					'price'     => $item->price(),
					'subtotal'  => $item->subtotal(),
					'attendees' => $item->attendees(),
				];
			}
		}

		$response->getBody()->write(
			$this->templates->render('cart/index', [
				'items'    => $enriched,
				'subtotal' => $cart->subtotal(),
				'isEmpty'  => $cart->isEmpty(),
			])
		);

		return $response;
	}
}
