# New Project Setup Guide

This guide helps you spin up a new app from this starter with minimal friction.

## 5-minute quickstart (recommended: DDEV)

Prereqs:

- DDEV
- Docker Desktop (or compatible Docker runtime)

1. Copy this starter into a new folder and rename as needed.
2. From the project root, copy env defaults:

```bash
cp .env.example .env
```

3. Start containers:

```bash
ddev start
```

4. Install PHP and JS dependencies:

```bash
ddev composer install
ddev npm install
```

5. Seed sqlite data:

```bash
ddev composer seed:sqlite
```

6. Start Vite:

```bash
ddev npm run dev
```

7. Open the app:

- App: `https://<your-ddev-project>.ddev.site`
- Users JSON: `https://<your-ddev-project>.ddev.site/users`

8. Optional email smoke test:

```bash
ddev composer mail:test -- you@example.test "Starter mail check"
```

Open Mailpit with `ddev launch -m`.

## Local setup (no DDEV)

Prereqs:

- PHP 8.2+
- Composer
- Node.js 20+

1. Copy env defaults:

```bash
cp .env.example .env
```

2. Install dependencies:

```bash
composer install
npm install
```

3. Seed sqlite:

```bash
composer seed:sqlite
```

4. Run PHP and Vite in separate terminals:

```bash
php -S localhost:8080 -t public
npm run dev
```

5. Open `http://localhost:8080`.

## New-project checklist

Do this right after the first successful run:

1. Update `.env` values:
- `APP_NAME`
- `APP_URL`
- `MAIL_FROM`
- DB settings if not using sqlite

2. Replace seed/demo routes as needed:
- `/users`
- debug routes under `/debug/*`

3. Keep debug off outside local dev:
- Set `APP_DEBUG=false` in non-dev environments

4. Set up your initial providers in `config/bootstrap.php`:
- service providers for project services
- middleware providers for auth, rate limiting, etc.

## Troubleshooting

- `users` route fails with DB errors:
Run `composer seed:sqlite` (or `ddev composer seed:sqlite`) and confirm `DB_DATABASE` path exists.

- Styles/scripts not updating:
Make sure Vite is running (`npm run dev` or `ddev npm run dev`) and port `5173` is exposed for DDEV.

- Mail test fails:
Check `MAILER_DSN` in `.env`. In DDEV defaults it should be `smtp://127.0.0.1:1025`.
