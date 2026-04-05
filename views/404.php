<?php $this->layout('layout', ['title' => $title ?? 'Page not found']); ?>

<h1><?= $this->e($title ?? 'Page not found') ?></h1>
<p><?= $this->e($message ?? 'The page you requested could not be found.') ?></p>
