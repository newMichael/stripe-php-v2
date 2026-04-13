import { initStripe, appearanceOptions } from "../core/stripe.js";

async function cartCheckoutPage(publishableKey, subtotal) {
	const amountInCents = Math.round(subtotal * 100);
	const { stripe } = await initStripe(publishableKey);

	const elements = stripe.elements({
		mode: "payment",
		amount: amountInCents,
		currency: "usd",
		appearance: appearanceOptions,
	});
	const paymentElement = elements.create("payment");
	paymentElement.mount("#payment-element");
	const addressElement = elements.create("address", {
		mode: "billing",
		display: {
			name: "split",
		},
	});
	addressElement.mount("#address-element");
	const linkAuthenticationElement = elements.create("linkAuthentication");
	linkAuthenticationElement.mount("#link-authentication-element");

	elementsReady(paymentElement, addressElement, linkAuthenticationElement)
		.then(() => {
			console.log("Elements are ready");
		})
		.catch((error) => {
			console.error("Error waiting for elements to be ready:", error);
		});

	listenForSubmit(stripe, elements);
}

function elementsReady(...elements) {
	return Promise.all(
		elements.map(
			(element) =>
				new Promise((resolve) => {
					const onReady = () => {
						element.off("ready", onReady);
						resolve();
					};
					element.on("ready", onReady);
				}),
		),
	);
}

function listenForSubmit(stripe, elements) {
	const form = document.getElementById("payment-form");
	const submitButton = document.getElementById("cart-submit-button");
	const message = document.getElementById("payment-message");

	form.addEventListener("submit", async (e) => {
		e.preventDefault();

		submitButton.disabled = true;
		message.hidden = true;

		const { error: submitError } = await elements.submit();
		if (submitError) {
			message.textContent = submitError.message;
			message.hidden = false;
			submitButton.disabled = false;
			return;
		}

		// Create the payment intent server-side
		const response = await fetch("/cart/checkout/intent", {
			method: "POST",
			headers: { "Content-Type": "application/json" },
			body: JSON.stringify({}),
		});

		const { clientSecret } = await response.json();
		console.log("Client secret received:", clientSecret);

		const { error } = await stripe.confirmPayment({
			elements: elements,
			clientSecret,
			confirmParams: {
				return_url: `${window.location.origin}/cart/confirmation`,
			},
		});

		if (error) {
			message.textContent = error.message;
			message.hidden = false;
			submitButton.disabled = false;
		}
	});
}

// async function mountPaymentForm(publishableKey, subtotal) {
// 	const amountInCents = Math.round(subtotal * 100);

// 	const { stripe, initElements, createElements } =
// 		await initStripe(publishableKey);

// 	const elements = initElements(amountInCents);
// 	const { paymentElement } = createElements(elements);
// 	const paymentElementInstance = paymentElement("#payment-element");

// 	const form = document.getElementById("payment-form");
// 	const submitBtn = document.getElementById("submit-btn");
// 	const message = document.getElementById("payment-message");

// 	form.addEventListener("submit", async (e) => {
// 		e.preventDefault();

// 		submitBtn.disabled = true;
// 		message.hidden = true;

// 		// Validate the Payment Element before hitting the server
// 		const { error: submitError } = await elements.submit();
// 		if (submitError) {
// 			message.textContent = submitError.message;
// 			message.hidden = false;
// 			submitBtn.disabled = false;
// 			return;
// 		}

// 		// Create the payment intent server-side
// 		const response = await fetch("/payment/intent", {
// 			method: "POST",
// 			headers: { "Content-Type": "application/json" },
// 			body: JSON.stringify({ amount: amountInCents }),
// 		});

// 		const { clientSecret } = await response.json();

// 		const { error } = await stripe.confirmPayment({
// 			elements: elements,
// 			clientSecret,
// 			confirmParams: {
// 				return_url: `${window.location.origin}/cart/confirmation`,
// 			},
// 		});

// 		if (error) {
// 			message.textContent = error.message;
// 			message.hidden = false;
// 			submitBtn.disabled = false;
// 		}
// 	});
// }

const form = document.getElementById("payment-form");
// if (form) {
// 	mountPaymentForm(form.dataset.stripeKey, Number(form.dataset.subtotal));
// }

if (form) {
	cartCheckoutPage(form.dataset.stripeKey, Number(form.dataset.subtotal));
}
