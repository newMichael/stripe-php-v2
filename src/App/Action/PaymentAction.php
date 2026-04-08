<?php

declare(strict_types=1);

namespace App\Action;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class PaymentAction
{
	public function createPaymentIntent(Request $request, Response $response): Response
	{
		$data = json_decode((string) $request->getBody(), true);

		return $response;
	}
}
