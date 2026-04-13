<?php $this->layout('layout', ['title' => 'Checkout']); ?>

<h1>Checkout</h1>

<p>Total: $<?= number_format($subtotal, 2) ?></p>

<form id="payment-form"
	data-stripe-key="<?= $this->e($stripePublishableKey) ?>"
	data-subtotal="<?= $this->e($subtotal) ?>">
	<div class="stack">
		<div id="payment-element"></div>
		<div id="link-authentication-element"></div>
		<div id="address-element"></div>
		<button id="cart-submit-button" type="submit">Pay $<?= number_format($subtotal, 2) ?></button>
	</div>
	<div id="payment-message" hidden></div>
</form>