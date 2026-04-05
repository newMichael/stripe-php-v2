<?php

declare(strict_types=1);

namespace App\View;

final class Vite
{
	private ?array $manifest = null;

	public function __construct(private readonly string $projectRoot)
	{
	}

	public function tags(string $entrypoint): string
	{
		$entrypoint = ltrim($entrypoint, '/');
		$devServerUrl = $this->devServerUrl();
		if ($devServerUrl !== null) {
			$base = rtrim($devServerUrl, '/');
			return sprintf(
				'<script type="module" src="%1$s/@vite/client"></script>' . PHP_EOL .
				'<script type="module" src="%1$s/%2$s"></script>',
				$this->escape($base),
				$this->escape($entrypoint)
			);
		}

		$manifest = $this->loadManifest();
		if ($manifest === null || !isset($manifest[$entrypoint])) {
			return '';
		}

		$entry = $manifest[$entrypoint];
		$assets = [];
		$styles = $this->collectStyles($manifest, $entry);
		foreach ($styles as $style) {
			$assets[] = sprintf('<link rel="stylesheet" href="%s">', $this->escape('/build/' . ltrim($style, '/')));
		}

		if (isset($entry['file']) && is_string($entry['file'])) {
			$assets[] = sprintf(
				'<script type="module" src="%s"></script>',
				$this->escape('/build/' . ltrim($entry['file'], '/'))
			);
		}

		return implode(PHP_EOL, $assets);
	}

	private function devServerUrl(): ?string
	{
		$hotFile = $this->projectRoot . '/storage/vite.hot';
		if (!is_file($hotFile)) {
			return null;
		}

		$raw = trim((string) file_get_contents($hotFile));
		if ($raw === '') {
			return null;
		}

		return $this->isServerReachable($raw) ? $raw : null;
	}

	private function isServerReachable(string $url): bool
	{
		$parts = parse_url($url);
		if (!is_array($parts) || !isset($parts['host'])) {
			return false;
		}

		$scheme = $parts['scheme'] ?? 'http';
		$host = (string) $parts['host'];
		$port = (int) ($parts['port'] ?? ($scheme === 'https' ? 443 : 80));
		$target = $scheme === 'https' ? 'tls://' . $host : $host;

		$socket = @fsockopen($target, $port, $errorCode, $errorMessage, 0.15);
		if (!is_resource($socket)) {
			return false;
		}

		fclose($socket);
		return true;
	}

	private function loadManifest(): ?array
	{
		if ($this->manifest !== null) {
			return $this->manifest;
		}

		$manifestPath = $this->projectRoot . '/public/build/.vite/manifest.json';
		if (!is_file($manifestPath)) {
			return null;
		}

		$decoded = json_decode((string) file_get_contents($manifestPath), true);
		if (!is_array($decoded)) {
			return null;
		}

		$this->manifest = $decoded;
		return $this->manifest;
	}

	private function collectStyles(array $manifest, array $entry): array
	{
		$styles = [];
		$stack = [$entry];
		while ($stack !== []) {
			$current = array_pop($stack);
			$cssFiles = $current['css'] ?? [];
			if (is_array($cssFiles)) {
				foreach ($cssFiles as $cssFile) {
					if (is_string($cssFile)) {
						$styles[] = $cssFile;
					}
				}
			}

			$imports = $current['imports'] ?? [];
			if (!is_array($imports)) {
				continue;
			}

			foreach ($imports as $import) {
				if (is_string($import) && isset($manifest[$import]) && is_array($manifest[$import])) {
					$stack[] = $manifest[$import];
				}
			}
		}

		return array_values(array_unique($styles));
	}

	private function escape(string $value): string
	{
		return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	}
}
