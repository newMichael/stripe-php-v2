<?php

declare(strict_types=1);

use App\Action\CartAction;
use App\Action\DonationAction;
use App\Action\EventAction;
use App\Action\HomeAction;
use App\Action\PaymentAction;

$app->get('/', HomeAction::class);
$app->get('/donate', DonationAction::class);
$app->get('/events', EventAction::class);
$app->get('/events/ticket/{id}', EventAction::class . ':ticketDetails');
$app->get('/events/{slug}', EventAction::class . ':detailPage');
$app->post('/events/cart/add', EventAction::class . ':addToCart');
$app->post('/payment/intent', PaymentAction::class . ':createPaymentIntent');

$app->get('/cart', CartAction::class . ':view');
$app->post('/cart/remove', CartAction::class . ':remove');
$app->post('/cart/clear', CartAction::class . ':clear');
