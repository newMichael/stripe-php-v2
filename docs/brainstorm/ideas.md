# Project Architecture & Planning

## Overview

This is a standalone PHP ecommerce package (`src/Ecommerce/`) paired with JS modules (`resources/`). It handles three order types — **donations**, **event tickets**, and **memberships** — all flowing through a unified order system backed by Stripe. No MVC/router assumptions; the package is used as a library called directly from PHP pages.

---

## Database Schema Observations

The schema is fixed. Key things to work with:

- **`patrons`** — serves as both guest and account holder tracking. Email is unique, so repeat customers are found by email.
- **`orders`** — central record. `subscription_id` is nullable (only set for recurring). `order_fee` stores the Stripe passthrough fee if opted in.
- **`order_items`** — `item_metadata` (JSON) is how we distinguish item types. One flexible field to encode all item-specific data.
- **`order_payments`** — records Stripe payment method and status. One order can have multiple payments (e.g., initial + future recurring charges).
- **`subscriptions`** — stores `stripe_subscription_id` and links back to patron and original order.
- **`events` / `event_tickets`** — events are identified by slug. Tickets track quantity directly on the `event_tickets` table.
- **No `memberships` table** — membership data lives in `order_items.item_metadata` and `subscriptions`. A `membership_levels` table will be added (see Resolved Decisions below).

### `order_items` Columns

`item_type` and `reference_id` are first-class columns, not stored in metadata:

| `item_type`    | `reference_id`          |
|----------------|-------------------------|
| `donation`     | `NULL`                  |
| `event_ticket` | `event_tickets.ticket_id` |
| `membership`   | `membership_levels.membership_id` |

### `item_metadata` JSON Schema

`item_metadata` holds supplemental data that doesn't belong in a dedicated column:

```json
// Donation
{
  "frequency": "one-time",
  "in_honor_of": "Jane Doe",
  "in_memory_of": null,
  "anonymous": false
}

// Event Ticket — no extra metadata needed beyond reference_id
null

// Membership
{
  "frequency": "recurring"
}
```

---

## PHP Package Architecture (`src/Ecommerce/`)

```
src/Ecommerce/
├── Cart/
│   └── Cart.php                   # Cart session management (already started)
│   └── CartItem.php               # Value object for items in the cart
├── Order/
│   ├── Order.php                  # Order model
│   ├── OrderItem.php              # OrderItem model
│   ├── OrderRepository.php        # DB read/write for orders + items
│   └── OrderStatus.php            # Enum: pending, complete, refunded, cancelled
├── Patron/
│   ├── Patron.php                 # Patron model
│   └── PatronRepository.php       # Find-or-create by email
├── Payment/
│   ├── PaymentService.php         # Stripe PaymentIntent + Subscription creation (called at submit time, not on page load)
│   ├── FeeCalculator.php          # Stripe fee passthrough math
│   └── FreeOrderProcessor.php     # Handle $0 orders without Stripe
├── Donation/
│   └── DonationService.php        # Build + process a donation order
├── Ticket/
│   ├── TicketService.php          # Build + process ticket orders
│   └── TicketInventory.php        # Decrement/check availability
├── Membership/
│   └── MembershipService.php      # Build + process membership orders
├── Webhook/
│   └── WebhookHandler.php         # Handle Stripe webhook events
├── Mail/
│   └── MailService.php            # Send receipts, failure notices, refunds
└── Admin/
    ├── OrderAdmin.php             # List orders, issue refunds/cancellations
    └── InventoryAdmin.php         # Manage ticket quantities
```

---

## JS Modules (`resources/js/`)

```
resources/js/
├── stripe-init.js           # Load Stripe.js, initialize Elements
├── donation-form.js         # Preset/custom amounts, fee opt-in, recurring toggle, honor/memory fields
├── cart.js                  # Add/remove/update cart items, persist to session
├── checkout.js              # Checkout form, Stripe Elements card input, submit
└── ticket-inline.js         # "Buy now" inline ticket purchase (no cart)
```

---

## Core Flows

### 1. Donation Flow
1. User selects amount (preset or custom), frequency (one-time/recurring), fee opt-in, and optional honor/memory fields
2. Stripe Elements mounts immediately on page load (no PaymentIntent yet)
3. User fills in the form and clicks submit
4. JS calls PHP to create the PaymentIntent (or Subscription) at submit time with the final amount
5. JS confirms the payment using the returned `client_secret` — no page redirect needed
6. On Stripe confirmation, PHP records: patron → order → order_item → order_payment
7. For recurring: PHP creates a Stripe Customer + Subscription; stores `stripe_customer_id` + `stripe_subscription_id` in `subscriptions`
8. Confirmation email sent via Symfony Mailer

**Fee passthrough formula:** To have the org net `$amount`, charge `(amount + 0.30) / (1 - 0.029)` and store the difference in `order_fee`.

### 2. Event Ticket Flow
- **Cart checkout**: User adds tickets, goes to cart, fills name/email, checks out
- **Inline purchase**: "Buy tickets" directly on event page, no cart step
- Free tickets: skip payment, still record patron + order
- Inventory: decrement `ticket_quantity` on `event_tickets` on successful order; admin can adjust

### 3. Membership Flow
1. User selects membership level, frequency (one-time or recurring/annual auto-renew)
2. Optionally log in / create account (optional — guests allowed)
3. Checkout flow similar to donation

### 4. Recurring Payments (Webhooks)
Stripe sends webhook events; `WebhookHandler.php` processes them:
- `invoice.payment_succeeded` → record new `order_payment`, send receipt email
- `invoice.payment_failed` → send failure/update-payment email to patron
- `customer.subscription.deleted` → update `subscription_status` to cancelled

### 5. Admin
- **Order list**: query `orders` joined with `patrons`, filterable by type (via `item_metadata`)
- **Refund/cancel**: call Stripe Refunds API, update `order_status` to `refunded`, send email
- **Inventory**: update `ticket_quantity` on `event_tickets`

---

## Key Design Decisions

### No router assumption
The `Ecommerce` package services and repositories are instantiated directly in PHP page files. The Slim app in `src/App/` is the demo/dev harness; the package itself shouldn't depend on it.

### Patron identity
Patrons are identified by email. `PatronRepository::findOrCreate(email, fname, lname)` handles both new guests and returning customers transparently.

### PaymentIntent vs Subscription
- One-time payments → Stripe PaymentIntent
- Recurring donations/memberships → Stripe Subscription with a Price object (created dynamically since amounts can be custom)

### Webhook security
Stripe webhook endpoint must verify the `Stripe-Signature` header using the webhook secret to prevent spoofing.

### Free orders
`FreeOrderProcessor` skips all Stripe calls, sets `order_status = 'complete'` directly, and sends a confirmation email.

---

## Implementation Phases

### Phase 1 — Core Infrastructure
- `Patron`, `PatronRepository`
- `Order`, `OrderItem`, `OrderRepository`
- `PaymentService` (PaymentIntent creation)
- `FeeCalculator`
- Database PDO setup in the package

### Phase 2 — Donations
- `DonationService`
- Donation form JS (amounts, fee opt-in, honor/memory, recurring toggle)
- One-time + recurring flows
- Confirmation email

### Phase 3 — Event Tickets
- `TicketService`, `TicketInventory`
- Cart + checkout JS
- Inline ticket purchase JS
- Free ticket flow
- Admin inventory management

### Phase 4 — Memberships
- `MembershipService`, `MembershipLevelRepository`
- Add `membership_levels` table to schema + seed data
- Membership level selection UI (reads from DB)
- One-time + recurring flows

### Phase 5 — Webhooks & Emails
- `WebhookHandler` (payment_succeeded, payment_failed, subscription.deleted)
- `MailService` (receipt, failure notice, refund notice)
- Webhook endpoint wired up

### Phase 6 — Admin
- `OrderAdmin` (list, refund, cancel)
- `InventoryAdmin`
- Admin view templates
- Fake auth guard (`AdminAuthMiddleware` + `.env` credentials) for demo

---

## Resolved Decisions

- **Membership levels**: Stored in a `membership_levels` DB table. Schema needs one new table added (existing tables remain unchanged):
  ```sql
  CREATE TABLE `membership_levels` (
    `membership_id` int(11) NOT NULL AUTO_INCREMENT,
    `membership_title` varchar(100) NOT NULL,
    `membership_price` decimal(10,2) NOT NULL,
    PRIMARY KEY (`membership_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
  ```
  A `MembershipLevelRepository` will read from this table.

- **Stripe Customer (recurring billing)**: Stripe Subscriptions require a Customer object — created transparently using the patron's email, no login needed. `stripe_customer_id` is stored directly in the `subscriptions` table (column added). Flow for recurring: create Stripe Customer → create Subscription → store both IDs.

- **Cart persistence**: `Cart` will support both session and database backends via a `CartStorageInterface`. Session is the primary implementation; DB storage is stubbed for future use. The Cart class will be storage-agnostic.

- **Admin auth**: A lightweight fake auth guard (username/password in `.env`) will be used in the demo. In production the existing auth layer wraps the admin pages. A simple `AdminAuthMiddleware` or gate check at the top of admin page files.

- **Coupon codes** (future): `order_discount` column is already on `orders`, so the model will have a `discount` field ready. The coupon code lookup logic is deferred.

---

## Open Questions

- None at this time.
