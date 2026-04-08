# Project Requirements
Here is an outline of big picture concepts. See "User Stories" below for specific project requirements.

## PHP Ecommerce Package
- A standalone PHP package for ecommerce functionality. This will live in `src/Ecommerce/`.
- This will exclusively use Stripe for payment processing, but free orders will also be supported.

## JS Ecommerce Modules
- A set of JavaScript modules for ecommerce functionality. This will live in `src/js/`.
- This will handle the frontend interactions and will communicate with the PHP package for backend processing.
- Loads the Stripe JS library for payment processing using Stripe Elements.

## Existing constraints and considerations
- MariaDB table structure is already defined and cannot be changed. See @storage/schema.sql for details.
- Codebases that this will be used on do not use a router, controllers, or any typical MVC structure. Something to keep in mind for the surface area for how the PHP package will be used.

## User Stories
- As a user, I need to be able to submit a donation on a donation form as a guest without creating an account.
	- Donations can be one-time or recurring. Preset options are provided, but a custom amount can also be entered.
	- Processing fees can be opted into by the user, and the total will cover Stripe's fees so the organization receives the full intended amount.
	- Donations can be made in honor/memory of someone, and anonymous donations are also supported.
- As a user, I will receive an email receipt after each recurring donation is processed. 
- As a user, I need to be able to add event tickets to the cart and check out as a guest without creating an account.
	- Tickets can be for free or paid events. Free tickets will not require payment information, but will still require the user to fill out their name and email address.
	- For paid tickets, the user can select a quantity of tickets to purchase, and the quantity remaining will update accordingly.
- As a user, I need to be able to purchase or renew a membership from a list of membership levels.
	- Memberships can be purchased with or without a registered account.
	- Memberships can be one-time or recurring. Memberships last for a year.
- As a user, I need to be able to purchase an event ticket directly on the page without needing to go to the cart.
- As a user, I will receive a confirmation email / receipt after a successful purchase or donation.
- As a site admin, I need to be able to cancel or refund an order from an admin dashboard. Refunding an order will send a notification email to the user letting them know their order has been refunded.
- As a site admin, I need to be able to manage the inventory of event tickets from an admin dashboard, setting the quantity available.
- As a site admin, I need to be able to view a list of orders and donations in an admin dashboard.
- As a user, I will receive an email notification if a recurring donation or membership payment fails, and I will be prompted to update my payment information to avoid interruption of my donation or membership.

# Future User Stories
- As a user, I need to be able to enter a coupon code on the cart page and have it apply the appropriate discount to my order total.
