<?php

declare(strict_types=1);

namespace App\Action;

use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class UsersAction
{
	public function __construct(private readonly PDO $pdo)
	{
	}

	public function __invoke(Request $request, Response $response): Response
	{
		$statement = $this->pdo->query(
			'SELECT user_id, name, email, created_at FROM users ORDER BY user_id ASC'
		);

		$payload = [
			'users' => $statement->fetchAll(),
		];

		$response->getBody()->write(json_encode($payload, JSON_THROW_ON_ERROR));

		return $response->withHeader('Content-Type', 'application/json');
	}
}
