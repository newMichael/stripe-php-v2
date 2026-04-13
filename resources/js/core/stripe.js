import { loadStripe } from "@stripe/stripe-js";

let stripe = null;

export async function initStripe(publishableKey) {
	if (!stripe) {
		stripe = await loadStripe(publishableKey);
	}

	return {
		/** @type {import("@stripe/stripe-js").Stripe} */
		stripe: stripe,
		defaultOptions: {
			elementsOptions: function (amountInCents) {
				return {
					mode: "payment",
					amount: amountInCents,
					currency: "usd",
					appearance: appearanceOptions,
				};
			},
			createElementOptions: function () {
				return {
					paymentOptions: {
						layout: "accordion",
					},
					addressOptions: {
						mode: "billing",
						fields: {
							phone: "always",
							email: "always",
							name: "always",
							address: {
								line1: "always",
								line2: "optional",
								city: "always",
								state: "always",
								postal_code: "always",
								country: "always",
							},
						},
					},
				};
			},
		},
	};
}

export const appearanceOptions = {
	theme: "night",
};
