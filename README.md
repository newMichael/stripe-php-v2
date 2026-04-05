# slim-skelly

Slim 4 starter with:

- Typed app/database config
- League Container + Plates templates
- Vite asset pipeline
- SQLite seed script
- Symfony Mailer test flow
- CSRF + method-override middleware defaults

## Requirements

- PHP 8.2+
- Composer
- Node.js 20+
- Optional: DDEV + Docker

## Quick Start

Recommended:

```bash
bin/setup
```

`bin/setup` auto-detects DDEV when `.ddev/` exists and `ddev` is installed.

### Setup options

```bash
bin/setup --ddev
bin/setup --local
bin/setup --no-seed
bin/setup --force-env
```

## Run the app

### DDEV

```bash
ddev npm run dev
```

Open your DDEV URL (for example `https://<project>.ddev.site`).

### Local (no DDEV)

Run in separate terminals:

```bash
npm run dev
php -S localhost:8080 -t public
```

Open `http://localhost:8080`.

## Useful commands

```bash
composer seed:sqlite
composer mail:test -- you@example.test "Mail check"
```

Debug-only demo routes (`APP_DEBUG=true`):

- `/debug/security-demo` (CSRF + method override form demo)
- `/debug/email/send`
- `/debug/error/html`
- `/debug/error/json`

## Docs

- [Bootstrap lifecycle](docs/bootstrap.md)
- [Asset pipeline](docs/assets.md)
- [Email setup](docs/email.md)
- [New project setup](docs/new-project-setup.md)

## Before creating a real app from this starter

1. Update `.env` values (`APP_NAME`, `APP_URL`, `MAIL_FROM`, DB settings).
2. Remove or replace demo/debug routes you do not need.
3. Keep `APP_DEBUG=false` outside local development.
