<?php

declare(strict_types=1);

namespace App\Bootstrap;

use App\Bootstrap\Contracts\MiddlewareProviderInterface;
use App\Bootstrap\Contracts\ServiceProviderInterface;
use App\Config\AppConfig;
use InvalidArgumentException;
use League\Container\Container;
use RuntimeException;
use Slim\App;

final class ProjectExtensionRegistrar
{
	public function register(
		Container $container,
		App $app,
		AppConfig $config,
		array $extensions
	): void {
		$this->registerServiceProviders(
			$container,
			$config,
			$this->normalize($extensions['service_providers'] ?? [], 'service_providers')
		);

		$this->registerMiddlewareProviders(
			$app,
			$config,
			$this->normalize($extensions['middleware_providers'] ?? [], 'middleware_providers')
		);
	}

	/**
	 * @return list<class-string>
	 */
	private function normalize(array $providers, string $key): array
	{
		$normalized = [];
		foreach ($providers as $providerClass) {
			if (!is_string($providerClass) || $providerClass === '') {
				throw new InvalidArgumentException(
					sprintf('Each value in config/bootstrap.php[%s] must be a non-empty class string.', $key)
				);
			}
			$normalized[] = $providerClass;
		}

		return $normalized;
	}

	/**
	 * @param list<class-string<ServiceProviderInterface>> $providerClasses
	 */
	private function registerServiceProviders(Container $container, AppConfig $config, array $providerClasses): void
	{
		foreach ($providerClasses as $providerClass) {
			$provider = $this->buildProvider($providerClass, ServiceProviderInterface::class);
			$provider->register($container, $config);
		}
	}

	/**
	 * @param list<class-string<MiddlewareProviderInterface>> $providerClasses
	 */
	private function registerMiddlewareProviders(App $app, AppConfig $config, array $providerClasses): void
	{
		foreach ($providerClasses as $providerClass) {
			$provider = $this->buildProvider($providerClass, MiddlewareProviderInterface::class);
			$provider->register($app, $config);
		}
	}

	private function buildProvider(string $providerClass, string $interface): object
	{
		if (!class_exists($providerClass)) {
			throw new RuntimeException(sprintf('Provider class "%s" does not exist.', $providerClass));
		}

		$provider = new $providerClass();
		if (!$provider instanceof $interface) {
			throw new RuntimeException(
				sprintf('Provider class "%s" must implement %s.', $providerClass, $interface)
			);
		}

		return $provider;
	}
}
