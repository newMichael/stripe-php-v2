<?php

declare(strict_types=1);

use App\Action\Cart\CheckoutAction;
use App\Action\Cart\CheckoutIntentAction;
use App\Action\Cart\ClearCartAction;
use App\Action\Cart\ConfirmationAction;
use App\Action\Cart\RemoveCartItemAction;
use App\Action\Cart\ViewCartAction;
use App\Action\Donation\DonateIntentAction;
use App\Action\Donation\DonationConfirmationAction;
use App\Action\DonationAction;
use App\Action\Event\ListEventsAction;
use App\Action\Event\ViewEventAction;
use App\Action\HomeAction;
use App\Action\Payment\StripeWebhookAction;
use App\Action\Tickets\AddTicketToCartAction;
use App\Action\Tickets\ViewTicketAction;

$app->get('/', HomeAction::class);
$app->get('/donate', DonationAction::class);
$app->post('/donate/intent', DonateIntentAction::class);
$app->get('/donate/confirmation', DonationConfirmationAction::class);
$app->get('/events', ListEventsAction::class);
$app->get('/events/{slug}', ViewEventAction::class);
$app->get('/events/ticket/{id}', ViewTicketAction::class);
$app->post('/events/cart/add', AddTicketToCartAction::class);

$app->get('/cart', ViewCartAction::class);
$app->get('/cart/checkout', CheckoutAction::class);
$app->get('/cart/confirmation', ConfirmationAction::class);
$app->post('/cart/checkout/intent', CheckoutIntentAction::class);
$app->post('/cart/remove', RemoveCartItemAction::class);
$app->post('/cart/clear', ClearCartAction::class);

$app->post('/stripe/webhook', StripeWebhookAction::class);
