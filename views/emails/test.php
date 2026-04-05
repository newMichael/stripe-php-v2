<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title><?= $this->e($subject) ?></title>
</head>
<body style="font-family: Arial, sans-serif; color: #222;">
	<h1><?= $this->e($appName) ?> test email</h1>
	<p>This is a minimal test email rendered with Plates.</p>
	<ul>
		<li><strong>To:</strong> <?= $this->e($to) ?></li>
		<li><strong>Sent at:</strong> <?= $this->e($sentAt->format(DATE_ATOM)) ?></li>
	</ul>
</body>
</html>
