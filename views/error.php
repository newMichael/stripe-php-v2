<?php $this->layout('layout', ['title' => $title ?? 'Application error']); ?>

<article>
	<hgroup>
		<p>HTTP <?= $this->e((string) ($statusCode ?? 500)) ?></p>
		<h1><?= $this->e($title ?? 'Something went wrong') ?></h1>
	</hgroup>
	<p><?= $this->e($message ?? 'An unexpected error occurred. Please try again later.') ?></p>
</article>
