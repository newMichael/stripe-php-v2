<?php

/** @var string $appName */
/** @var array<int, array{
 *   method: string,
 *   pattern: string,
 *   label: string,
 *   description: string,
 *   handler: string,
 *   href: string,
 *   debug_only: bool,
 *   enabled: bool
 * }> $routes */
/** @var array{method: string, path: string, host: string} $request */
/** @var array{debug: string, phpVersion: string, timezone: string, mailerDsn: string, database: string} $system */
?>
<?php $this->layout('layout', ['title' => ($appName ?? 'Slim App') . ' Dashboard']); ?>

<header>
	<h1><?= $this->e($appName ?? 'Slim App') ?> Dashboard</h1>
	<p>Welcome screen for this starter app with quick route navigation and runtime details.</p>
</header>

<section>
	<h2>Routes</h2>
	<div class="overflow-auto">
		<table class="striped">
			<thead>
				<tr>
					<th>Method</th>
					<th>Pattern</th>
					<th>Link</th>
					<th>Purpose</th>
					<th>Handler</th>
					<th>Status</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach (($routes ?? []) as $route): ?>
					<tr>
						<td><code><?= $this->e($route['method']) ?></code></td>
						<td><code><?= $this->e($route['pattern']) ?></code></td>
						<td>
							<a href="<?= $this->e($route['href']) ?>"><?= $this->e($route['href']) ?></a>
						</td>
						<td><?= $this->e($route['description']) ?></td>
						<td><code><?= $this->e($route['handler']) ?></code></td>
						<td><?= $route['enabled'] ? 'available' : 'debug only' ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</section>

<section>
	<h2>Request</h2>
	<div class="overflow-auto">
		<table class="striped">
			<tbody>
				<tr>
					<th>Method</th>
					<td><code><?= $this->e($request['method'] ?? 'GET') ?></code></td>
				</tr>
				<tr>
					<th>Path</th>
					<td><code><?= $this->e($request['path'] ?? '/') ?></code></td>
				</tr>
				<tr>
					<th>Host</th>
					<td><code><?= $this->e($request['host'] ?? 'localhost') ?></code></td>
				</tr>
			</tbody>
		</table>
	</div>
</section>

<section>
	<h2>System</h2>
	<div class="overflow-auto">
		<table class="striped">
			<tbody>
				<tr>
					<th>Debug mode</th>
					<td><?= $this->e($system['debug'] ?? 'unknown') ?></td>
				</tr>
				<tr>
					<th>PHP version</th>
					<td><code><?= $this->e($system['phpVersion'] ?? '') ?></code></td>
				</tr>
				<tr>
					<th>Timezone</th>
					<td><code><?= $this->e($system['timezone'] ?? '') ?></code></td>
				</tr>
				<tr>
					<th>Database driver</th>
					<td><code><?= $this->e($system['database'] ?? '') ?></code></td>
				</tr>
				<tr>
					<th>Mailer DSN</th>
					<td><code><?= $this->e($system['mailerDsn'] ?? '') ?></code></td>
				</tr>
			</tbody>
		</table>
	</div>
</section>