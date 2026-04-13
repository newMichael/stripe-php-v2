import Alpine from "alpinejs";
import { initStripe, appearanceOptions } from "../core/stripe.js";

Alpine.data("donateForm", (stripeKey) => ({
	debug: new URLSearchParams(location.search).has("debug"),
	amountOption: "",
	otherAmount: "",
	frequencyOption: "one-time",
	submitting: false,
	message: "",

	get showOtherAmount() {
		return this.amountOption === "other";
	},

	get hasValidAmount() {
		return Boolean(this.getAmountInCents());
	},

	_stripe: null,
	_elements: null,
	_currentAmountInCents: null,
	_email: null,

	_debug(prop, value) {
		if (!this.debug) return;
		console.log(`[donateForm] ${prop} →`, value, {
			showOtherAmount: this.showOtherAmount,
			hasValidAmount: this.hasValidAmount,
		});
	},

	async init() {
		if (this.debug) {
			[
				"amountOption",
				"otherAmount",
				"frequencyOption",
				"submitting",
				"message",
			].forEach((prop) => {
				this.$watch(prop, (value) => this._debug(prop, value));
			});
		}
		this.$watch("amountOption", () => this.syncElements());
		this.$watch("otherAmount", () => this.syncElements());
		this.$watch("frequencyOption", () => this.syncElements());
		const { stripe } = await initStripe(stripeKey);
		this._stripe = stripe;
		await this.syncElements();
	},

	getAmountInCents() {
		if (!this.amountOption) return null;
		if (this.amountOption === "other") {
			const val = parseFloat(this.otherAmount);
			return isNaN(val) || val < 1 ? null : Math.round(val * 100);
		}
		return parseInt(this.amountOption, 10) * 100;
	},

	async initializeElements(amountInCents) {
		this._currentAmountInCents = amountInCents;
		this._elements = this._stripe.elements({
			mode: "payment",
			amount: amountInCents,
			currency: "usd",
			appearance: appearanceOptions,
		});

		const paymentEl = this._elements.create("payment");
		paymentEl.mount("[data-stripe-payment]");

		const addressEl = this._elements.create("address", {
			mode: "billing",
			display: { name: "split" },
		});
		addressEl.mount("[data-stripe-address]");

		const linkAuthEl = this._elements.create("linkAuthentication");
		linkAuthEl.mount("[data-stripe-link-auth]");
		linkAuthEl.on("change", (e) => {
			this._email = e.value.email;
		});

		await Promise.all([
			this._waitForReady(paymentEl),
			this._waitForReady(addressEl),
			this._waitForReady(linkAuthEl),
		]);
	},

	_waitForReady(element) {
		return new Promise((resolve) => {
			const onReady = () => {
				element.off("ready", onReady);
				resolve();
			};
			element.on("ready", onReady);
		});
	},

	_waitForElementsUpdate() {
		return new Promise((resolve) => {
			const onUpdateEnd = () => {
				this._elements.off("update-end", onUpdateEnd);
				resolve();
			};
			this._elements.on("update-end", onUpdateEnd);
		});
	},

	async syncElements() {
		const amountInCents = this.getAmountInCents();
		this.message = "";

		if (!amountInCents) return;

		if (!this._elements) {
			await this.initializeElements(amountInCents);
		} else if (amountInCents !== this._currentAmountInCents) {
			const updateComplete = this._waitForElementsUpdate();
			this._elements.update({ amount: amountInCents });
			await updateComplete;
			this._currentAmountInCents = amountInCents;
		}
	},

	async handleSubmit() {
		if (!this._elements) return;

		this.submitting = true;
		this.message = "";

		const { error: submitError } = await this._elements.submit();
		if (submitError) {
			this.message = submitError.message;
			this.submitting = false;
			return;
		}

		const amountInCents = this.getAmountInCents();

		try {
			const response = await fetch("/donate/intent", {
				method: "POST",
				headers: { "Content-Type": "application/json" },
				body: JSON.stringify({
					amount: amountInCents,
					frequency: this.frequencyOption,
					email: this._email,
				}),
			});

			const payload = await response.json();
			if (!response.ok) {
				throw new Error(payload.error ?? "Failed to create donation intent");
			}
			if (!payload.clientSecret) {
				throw new Error("Missing client secret from donation intent response");
			}

			const { error } = await this._stripe.confirmPayment({
				elements: this._elements,
				clientSecret: payload.clientSecret,
				confirmParams: {
					return_url: `${window.location.origin}/donate/confirmation`,
				},
			});

			if (error) {
				this.message = error.message;
				this.submitting = false;
			}
		} catch (err) {
			this.message =
				err instanceof Error ? err.message : "Unable to process donation";
			this.submitting = false;
		}
	},
}));
