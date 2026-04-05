const fs = require('node:fs');
const path = require('node:path');
const { defineConfig } = require('vite');

const hotFile = path.resolve(__dirname, 'storage/vite.hot');
const defaultPort = Number(process.env.VITE_PORT || 5173);

function resolveDevServerUrl(port) {
	if (process.env.VITE_DEV_SERVER_URL) {
		return process.env.VITE_DEV_SERVER_URL.replace(/\/$/, '');
	}

	if (process.env.DDEV_PRIMARY_URL) {
		return `${process.env.DDEV_PRIMARY_URL.replace(/\/$/, '')}:${port}`;
	}

	return `http://127.0.0.1:${port}`;
}

function hotFilePlugin(url) {
	return {
		name: 'hot-file',
		configureServer() {
			fs.mkdirSync(path.dirname(hotFile), { recursive: true });
			fs.writeFileSync(hotFile, url, 'utf8');
		},
		closeBundle() {
			if (fs.existsSync(hotFile)) {
				fs.rmSync(hotFile);
			}
		},
	};
}

module.exports = defineConfig(() => {
	const devServerUrl = resolveDevServerUrl(defaultPort);
	const parsedUrl = new URL(devServerUrl);

	return {
		plugins: [hotFilePlugin(devServerUrl)],
		server: {
			host: '0.0.0.0',
			port: defaultPort,
			strictPort: true,
			cors: true,
			origin: devServerUrl,
			hmr: {
				protocol: parsedUrl.protocol === 'https:' ? 'wss' : 'ws',
				host: parsedUrl.hostname,
				clientPort: Number(parsedUrl.port || (parsedUrl.protocol === 'https:' ? 443 : 80)),
			},
		},
		build: {
			manifest: true,
			outDir: 'public/build',
			emptyOutDir: true,
			rollupOptions: {
				input: {
					app: 'resources/js/app.js',
				},
			},
		},
	};
});
