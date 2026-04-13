<?php $this->layout('layout', ['title' => 'Donate Online']); ?>

<section>
	<h1>Donate Online</h1>
	<p>Use the form below to make a donation online.</p>

	<form id="donate-form"
		x-data="donateForm('<?= $this->e($stripePublishableKey) ?>')"
		method="post" action="/donate"
		@submit.prevent="handleSubmit()">
		<fieldset>
			<legend>Donation Amount</legend>
			<?php foreach ([10, 25, 50, 100] as $preset): ?>
				<label>
					<input type="radio" name="amount" value="<?= $preset ?>" x-model="amountOption">
					$<?= $preset ?>
				</label>
			<?php endforeach; ?>
			<label>
				<input type="radio" name="amount" value="other" x-model="amountOption">
				Other
			</label>

			<div x-show="showOtherAmount">
				<label for="amount_other">Other Amount</label>
				<input type="number" name="amount_other" id="amount_other" min="1" step="any" x-model="otherAmount">
			</div>
		</fieldset>

		<fieldset>
			<legend>Frequency</legend>
			<label>
				<input type="radio" name="frequency" value="one-time" x-model="frequencyOption">
				One-Time
			</label>
			<label>
				<input type="radio" name="frequency" value="monthly" x-model="frequencyOption">
				Monthly
			</label>
			<label>
				<input type="radio" name="frequency" value="yearly" x-model="frequencyOption">
				Yearly
			</label>
		</fieldset>

		<div>
			<input type="checkbox" name="anonymous" id="anonymous">
			<label for="anonymous">Anonymous donation</label>
		</div>

		<div id="cover-fees-container">
			<div>
				<input type="checkbox" name="cover_fees" id="cover_fees">
				<label for="cover_fees">Cover processing fees</label>
			</div>
			<p>Offset processing fees and add an additonal <span id="fees-amount-display"></span></p>
		</div>

		<div id="stripe-fields" x-show="hasValidAmount">
			<div data-stripe-link-auth></div>
			<div data-stripe-address></div>
			<div data-stripe-payment></div>
		</div>

		<button type="submit" :disabled="submitting || !hasValidAmount" x-text="submitting ? 'Processing...' : 'Donate'"></button>
		<div x-show="message" x-text="message"></div>
	</form>
</section>
