<?php

declare(strict_types=1);

namespace App\Action;

use League\Plates\Engine;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class DonationAction
{
	public function __construct(private readonly Engine $templates) {}

	public function __invoke(Request $request, Response $response): Response
	{
		$response->getBody()->write($this->templates->render('donate', [
			'title' => 'Donate',
			'stripePublishableKey' => $_ENV['STRIPE_PUBLIC_KEY'],
		]));
		return $response;
	}
}
