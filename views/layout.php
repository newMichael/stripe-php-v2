<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?= $this->e($title ?? 'Slim App') ?></title>
	<?= $this->vite('resources/js/app.js') ?>
</head>
<body>
	<main class="container">
		<?= $this->section('content') ?>
	</main>
</body>
</html>
