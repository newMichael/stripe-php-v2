# Email with Symfony Mailer

The app sends email through Symfony Mailer using `MAILER_DSN`.

## DDEV + Mailpit setup

`MAILER_DSN` defaults to `smtp://127.0.0.1:1025`, which matches Mailpit inside the DDEV web container.

## Test by HTTP route (debug only)

When `APP_DEBUG=true`, send a test email via:

```bash
ddev exec curl -sS "http://127.0.0.1/debug/email/send?to=you@example.test&subject=Mailpit%20check"
```

## Test by CLI

```bash
ddev composer mail:test -- you@example.test "Mailpit check"
```

## View delivered messages

Open Mailpit:

- `https://slim-skelly.ddev.site:8026`
- or run `ddev launch -m`
