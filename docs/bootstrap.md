# Bootstrap lifecycle

The app bootstrap sequence lives in `bootstrap/app.php` and follows this order:

1. Load env (`Dotenv`) and build `AppConfig`.
2. Build the DI container with core services.
3. Create Slim app from the container.
4. Register project extension providers from `config/bootstrap.php`.
5. Register framework middleware.
6. Enable debug tooling if `APP_DEBUG=true`.
7. Register error handlers.
8. Load routes (`src/routes.php`).

## Default middleware stack

`App\Bootstrap\MiddlewareRegistrar` registers these defaults:

- trailing slash normalization (`middlewares/trailing-slash`)
- body parsing middleware
- routing middleware
- HTTP method override middleware (`_METHOD` and `X-Http-Method-Override`)
- CSRF guard middleware (`slim/csrf`) when installed

CSRF uses session-backed token storage. Sessions are started during bootstrap before middleware registration.
CSRF failures use a custom handler that returns `403` with either HTML (`views/error.php`) or JSON based on the request `Accept` header.
When CSRF is enabled, `App\Middleware\CsrfViewDataMiddleware` also adds a normalized `csrf` request attribute for form-rendering actions/views.

## Typed configuration

- `App\Config\AppConfig` centralizes app-level config.
- `App\Config\DatabaseConfig` validates DB driver settings and required env values.
- Bootstrap and factories consume typed config instead of reading `$_ENV` directly.

## Extension points

Project-level extension hooks are configured in `config/bootstrap.php`.

Available keys:

- `service_providers`: classes implementing `App\Bootstrap\Contracts\ServiceProviderInterface`
- `middleware_providers`: classes implementing `App\Bootstrap\Contracts\MiddlewareProviderInterface`

Each provider must have a no-argument constructor.

Example:

```php
<?php

declare(strict_types=1);

use App\Provider\AppServicesProvider;
use App\Provider\ProjectMiddlewareProvider;

return [
	'service_providers' => [
		AppServicesProvider::class,
	],
	'middleware_providers' => [
		ProjectMiddlewareProvider::class,
	],
];
```

## Failure behavior

- Invalid provider config shape throws `InvalidArgumentException`.
- Missing provider classes throw `RuntimeException`.
- Provider classes not implementing the required interface throw `RuntimeException`.
