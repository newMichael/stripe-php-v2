<?php

declare(strict_types=1);

namespace App\Action;

use League\Plates\Engine;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use function is_array;

final class SecurityDemoAction
{
	public function __construct(private readonly Engine $templates)
	{
	}

	public function form(Request $request, Response $response): Response
	{
		$response->getBody()->write($this->templates->render('security-demo', [
			'title' => 'Security middleware demo',
			'csrf' => $this->csrfPayload($request),
		]));

		return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
	}

	public function patch(Request $request, Response $response): Response
	{
		return $this->jsonDemoResponse($request, $response, 'PATCH');
	}

	public function delete(Request $request, Response $response): Response
	{
		return $this->jsonDemoResponse($request, $response, 'DELETE');
	}

	/**
	 * @return array{name_key: string, value_key: string, name: string, value: string}
	 */
	private function csrfPayload(Request $request): array
	{
		$csrf = $request->getAttribute('csrf');
		if (!is_array($csrf)) {
			return [
				'name_key' => 'csrf_name',
				'value_key' => 'csrf_value',
				'name' => '',
				'value' => '',
			];
		}

		return [
			'name_key' => (string) ($csrf['name_key'] ?? 'csrf_name'),
			'value_key' => (string) ($csrf['value_key'] ?? 'csrf_value'),
			'name' => (string) ($csrf['name'] ?? ''),
			'value' => (string) ($csrf['value'] ?? ''),
		];
	}

	private function jsonDemoResponse(Request $request, Response $response, string $expected): Response
	{
		$payload = [
			'ok' => true,
			'expected_method' => $expected,
			'request_method' => $request->getMethod(),
			'path' => $request->getUri()->getPath(),
			'message' => sprintf('%s route reached via method override + CSRF validation.', $expected),
		];

		$response->getBody()->write((string) json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

		return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
	}
}
