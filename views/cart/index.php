<?php $this->layout('layout', ['title' => 'Your Cart']); ?>

<h1>Your Cart</h1>

<?php if ($isEmpty): ?>
	<p>Your cart is empty.</p>
<?php else: ?>
	<table>
		<thead>
			<tr>
				<th>Item</th>
				<th>Price</th>
				<th>Quantity</th>
				<th>Subtotal</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($items as $item): ?>
				<tr>
					<td>
						<?= $this->e($item['label']) ?>
						<?php if (!empty($item['attendees'])): ?>
							<ul>
								<?php foreach ($item['attendees'] as $attendee): ?>
									<li><?= $this->e($attendee['name']) ?> &lt;<?= $this->e($attendee['email']) ?>&gt;</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</td>
					<td>$<?= number_format($item['price'], 2) ?></td>
					<td><?= $item['quantity'] ?></td>
					<td>$<?= number_format($item['subtotal'], 2) ?></td>
					<td>
						<form method="post" action="/cart/remove">
							<input type="hidden" name="key" value="<?= $this->e($item['key']) ?>">
							<button type="submit">Remove</button>
						</form>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
		<tfoot>
			<tr>
				<th colspan="3">Subtotal</th>
				<td>$<?= number_format($subtotal, 2) ?></td>
				<td></td>
			</tr>
		</tfoot>
	</table>

	<div>
		<form method="post" action="/cart/clear">
			<button type="submit">Clear Cart</button>
		</form>

		<a href="/checkout" role="button">Proceed to Checkout</a>
	</div>
<?php endif; ?>
