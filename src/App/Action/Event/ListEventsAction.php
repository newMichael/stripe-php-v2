<?php

declare(strict_types=1);

namespace App\Action\Event;

use League\Plates\Engine;
use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Flash\Messages;

final class ListEventsAction
{
	public function __construct(
		private readonly Engine $templates,
		private readonly PDO $db,
		private readonly Messages $flash
	) {}

	public function __invoke(Request $request, Response $response): Response
	{
		$this->db->query('SELECT * FROM events');
		$events = $this->db->query('SELECT * FROM events')->fetchAll(PDO::FETCH_ASSOC);
		$response->getBody()->write($this->templates->render('events/overview', ['events' => $events]));
		return $response;
	}
}
