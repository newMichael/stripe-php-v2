<?php

declare(strict_types=1);

namespace App\Config;

use Slim\Interfaces\RouteCollectorInterface;
use Slim\Interfaces\RouteInterface;
use Stringable;

final class RouteCatalog
{
	/** @var array<string, array{label: string, description: string, sample_params?: array<string, string>}> */
	private const DASHBOARD_METADATA = [
		'/' => [
			'label' => 'Home dashboard',
			'description' => 'Starter welcome screen with route index and app metadata.',
		],
		'/hello/{name}' => [
			'label' => 'Hello action',
			'description' => 'Greets a path parameter.',
			'sample_params' => ['name' => 'world'],
		],
		'/health' => [
			'label' => 'Health check',
			'description' => 'Simple status endpoint.',
		],
		'/users' => [
			'label' => 'Users list',
			'description' => 'Loads users from the configured database.',
		],
		'/debug/email/send' => [
			'label' => 'Debug email sender',
			'description' => 'Triggers a test email in debug mode.',
		],
		'/debug/error/html' => [
			'label' => 'Debug HTML error',
			'description' => 'Throws a Bad Request exception (HTML output).',
		],
		'/debug/error/json' => [
			'label' => 'Debug JSON error',
			'description' => 'Throws a Bad Request exception (JSON output).',
		],
		'/debug/security-demo' => [
			'label' => 'Security demo form',
			'description' => 'Form demo for CSRF + HTTP method override.',
		],
		'/debug/security-demo/resource' => [
			'label' => 'Security demo submit',
			'description' => 'PATCH/DELETE target for method override demo.',
		],
	];

	/**
	 * @return array<int, array{
	 *     method: string,
	 *     pattern: string,
	 *     label: string,
	 *     description: string,
	 *     handler: string,
	 *     href: string,
	 *     debug_only: bool,
	 *     enabled: bool
	 * }>
	 */
	public static function dashboardRoutes(RouteCollectorInterface $routeCollector, bool $isDebug): array
	{
		return array_map(
			static function (RouteInterface $route) use ($isDebug): array {
				$pattern = $route->getPattern();
				$metadata = self::DASHBOARD_METADATA[$pattern] ?? [];
				$debugOnly = str_starts_with($pattern, '/debug/');
				$methods = array_values(array_filter(
					$route->getMethods(),
					static fn (string $method): bool => $method !== 'HEAD'
				));

				return [
					'method' => $methods[0] ?? 'GET',
					'pattern' => $pattern,
					'label' => $metadata['label'] ?? self::buildFallbackLabel($route),
					'description' => $metadata['description'] ?? 'Route endpoint.',
					'handler' => self::normalizeCallable($route->getCallable()),
					'href' => self::buildHref($pattern, $metadata['sample_params'] ?? []),
					'debug_only' => $debugOnly,
					'enabled' => !$debugOnly || $isDebug,
				];
			},
			$routeCollector->getRoutes()
		);
	}

	/**
	 * @param callable|array{class-string, string}|string $callable
	 */
	private static function normalizeCallable(mixed $callable): string
	{
		if (is_string($callable)) {
			return $callable;
		}

		if (is_array($callable)) {
			return implode('::', $callable);
		}

		if ($callable instanceof Stringable) {
			return (string) $callable;
		}

		return 'Closure';
	}

	private static function buildFallbackLabel(RouteInterface $route): string
	{
		$callable = self::normalizeCallable($route->getCallable());
		if (str_contains($callable, '\\')) {
			$parts = explode('\\', $callable);
			$callable = end($parts) ?: $callable;
		}

		return trim($callable) !== '' ? $callable : 'Route';
	}

	/**
	 * @param array<string, string> $sampleParams
	 */
	private static function buildHref(string $pattern, array $sampleParams): string
	{
		return preg_replace_callback(
			'/\{([^}:]+)(:[^}]*)?\}/',
			static function (array $matches) use ($sampleParams): string {
				$key = $matches[1];
				return $sampleParams[$key] ?? $key;
			},
			$pattern
		) ?? $pattern;
	}
}
