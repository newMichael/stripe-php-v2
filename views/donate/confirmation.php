<?php $this->layout('layout', ['title' => 'Thank You for Your Donation']); ?>

<?php if (!$success): ?>
	<h1>Something went wrong</h1>
	<p>Your payment could not be confirmed. Please <a href="/donate">try again</a> or contact us for help.</p>
<?php else: ?>
	<h1>Thank You!</h1>
	<?php
		$amount = '$' . number_format($amountInCents / 100, 2);
	?>
	<?php if ($frequency !== null): ?>
		<p>Your <?= $this->e($frequency) ?> donation of <strong><?= $this->e($amount) ?></strong> has been set up successfully.</p>
		<p>You will be charged <?= $this->e($amount) ?> <?= $this->e($frequency === 'monthly' ? 'each month' : 'each year') ?>. You'll receive a receipt by email for each payment.</p>
	<?php else: ?>
		<p>Your donation of <strong><?= $this->e($amount) ?></strong> has been successfully processed.</p>
		<p>A receipt has been sent to your email address. We appreciate your support!</p>
	<?php endif; ?>
<?php endif; ?>
