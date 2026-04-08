<?php $this->layout('layout', ['title' => $event['event_title']]); ?>

<h1><?= $this->e($event['event_title']) ?></h1>
<?php if (count($tickets) > 0): ?>
	<h2>Tickets</h2>
	<ul>
		<?php foreach ($tickets as $ticket): ?>
			<li><?= $this->e($ticket['ticket_title']) ?> - $<?= number_format($ticket['ticket_price'] / 100, 2) ?> USD</li>
		<?php endforeach; ?>
	</ul>

	<form method="get" id="ticket-select-form">
		<label for="ticket-option-select">Select a Ticket</label>
		<select name="ticket_option" id="ticket-option-select" required>
			<?php foreach ($tickets as $ticket): ?>
				<option value="<?= $this->e($ticket['ticket_id']) ?>"><?= $this->e($ticket['ticket_title']) ?> - $<?= number_format($ticket['ticket_price'] / 100, 2) ?> USD</option>
			<?php endforeach; ?>
		</select>
		<button id="register-button">Register</button>
	</form>

	<dialog id="ticket-registration-dialog">
		<article>
			<h2>Ticket Details</h2>
			<div id="ticket-details-content"></div>

			<form method="post" id="ticket-registration-form">
				<input type="hidden" name="ticket_id" id="selected-ticket-id" value="">
				<div>
					<label>Quantity</label>
					<input type="number" name="quantity" id="ticket-quantity" min="1" max="20" value="1" required>
				</div>
				<fieldset>
					<legend>Attendee Information</legend>
					<div id="attendees-fields-wrapper"></div>
				</fieldset>

				<button type="submit">Proceed to Payment</button>
			</form>

			<button id="close-dialog-button">Close</button>
		</article>
	</dialog>
<?php else: ?>
	<p>No tickets available for this event.</p>
<?php endif; ?>


<script>
	const ticketForm = document.getElementById('ticket-select-form');
	const ticketSelect = document.getElementById('ticket-option-select');

	ticketForm.addEventListener('submit', function(e) {
		e.preventDefault();
		const selectedTicketId = ticketSelect.value;

		fetch(`/events/ticket/${selectedTicketId}`)
			.then(response => response.json())
			.then(data => {
				showTicketDialog(data);
			})
			.catch(error => {
				console.error('Error fetching ticket details:', error);
			});
	});

	const dialog = document.getElementById('ticket-registration-dialog');
	const closeDialogButton = document.getElementById('close-dialog-button');
	const quantityInput = document.getElementById('ticket-quantity');

	closeDialogButton.addEventListener('click', function() {
		dialog.close();
	});

	quantityInput.addEventListener('change', function() {
		const quantity = parseInt(quantityInput.value, 10);
		renderAttendeesFields(quantity);
	});

	function showTicketDialog(ticketDetails) {
		const contentDiv = document.getElementById('ticket-details-content');
		contentDiv.innerHTML = `
			<p><strong>Title:</strong> ${ticketDetails.ticket_title}</p>
			<p><strong>Price:</strong> $${(ticketDetails.ticket_price / 100).toFixed(2)} USD</p>
		`;
		const selectedTicketIdInput = document.getElementById('selected-ticket-id');
		selectedTicketIdInput.value = ticketDetails.ticket_id;
		quantityInput.value = 1;
		renderAttendeesFields(1);
		dialog.showModal();
	}

	function renderAttendeesFields(quantity) {
		const wrapper = document.getElementById('attendees-fields-wrapper');
		wrapper.innerHTML = '';
		for (let i = 1; i <= quantity; i++) {
			const attendeeDiv = document.createElement('div');
			attendeeDiv.innerHTML = `
				<h3>Attendee ${i}</h3>
				<div>
					<label for="attendee-name-${i}">Name:</label>
					<input type="text" name="attendee[${i}][name]" id="attendee-name-${i}" required>
				</div>
				<div>
					<label for="attendee-email-${i}">Email:</label>
					<input type="email" name="attendee[${i}][email]" id="attendee-email-${i}" required>
				</div>
			`;
			wrapper.appendChild(attendeeDiv);
		}
	}
</script>