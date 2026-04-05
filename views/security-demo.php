<?php
/** @var array{name_key: string, value_key: string, name: string, value: string} $csrf */
?>
<?php $this->layout('layout', ['title' => 'Security middleware demo']); ?>

<header>
	<h1>CSRF + Method Override Demo</h1>
	<p>These forms submit with <code>method="post"</code> and use <code>_METHOD</code> to hit PATCH/DELETE routes.</p>
</header>

<section>
	<h2>PATCH override</h2>
	<form method="post" action="/debug/security-demo/resource">
		<?= $this->csrfInputs($csrf) ?>
		<?= $this->methodInput('PATCH') ?>
		<button type="submit">Submit as PATCH</button>
	</form>
</section>

<section>
	<h2>DELETE override</h2>
	<form method="post" action="/debug/security-demo/resource">
		<?= $this->csrfInputs($csrf) ?>
		<?= $this->methodInput('DELETE') ?>
		<button type="submit">Submit as DELETE</button>
	</form>
</section>

<section>
	<p><a href="/">Back to dashboard</a></p>
</section>
