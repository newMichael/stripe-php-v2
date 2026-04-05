# Vite assets

The app uses Vite to bundle frontend assets from `resources/` into `public/build/`.

Entrypoint used by templates:

- `resources/js/app.js` (imports `resources/css/app.css`)

## Install dependencies

```bash
npm install
```

## Local PHP server workflow

Run Vite in one terminal:

```bash
npm run dev
```

Run PHP in another terminal:

```bash
php -S localhost:8080 -t public
```

Open [http://localhost:8080](http://localhost:8080).

## DDEV workflow

Port `5173` is exposed via `.ddev/config.vite.yaml`.

1. Restart DDEV once so the extra port is applied:

```bash
ddev restart
```

2. Start Vite inside the web container:

```bash
ddev npm run dev
```

3. Open your site via DDEV (for example `https://slim-skelly.ddev.site`).

## Production build

```bash
npm run build
```

This generates versioned assets and `public/build/.vite/manifest.json`.
Templates automatically switch between:

- Vite dev server (when `storage/vite.hot` exists)
- Built files from the manifest (when dev server is not running)
