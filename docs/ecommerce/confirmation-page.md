# Handling Confirmation Pages with Stripe

When the route to the confirmation page is hit, what happens depends on if the request contains query parameters from Stripe's redirect after checkout:
- `payment_intent` the payment intent ID
- `payment_intent_client_secret` the client secret for the payment intent; only useful for client-side confirmation
- `redirect_status` not documented by Stripe, so no logic is built around it, but observed to be `succeeded` when the payment is successful

If the `payment_intent` query parameter is present, the confirmation page will attempt to retrieve the payment intent details from Stripe. If the retrieval is successful, it will check the status of the payment intent. If the status is `succeeded`, it will display a success message to the user. If the status is not `succeeded`:
	- `pending`: The payment is still processing, and the user should wait for a confirmation email to know the final status of their payment.
	- `failed`: The payment failed.
