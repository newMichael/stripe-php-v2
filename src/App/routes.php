<?php

declare(strict_types=1);

use App\Action\DonationAction;
use App\Action\EventAction;
use App\Action\HomeAction;
use App\Action\PaymentAction;

$app->get('/', HomeAction::class);
$app->get('/donate', DonationAction::class);
$app->get('/events', EventAction::class);
$app->get('/events/ticket/{id}', EventAction::class . ':ticketDetails');
$app->get('/events/{slug}', EventAction::class . ':detailPage');
$app->post('/payment/intent', PaymentAction::class . ':createPaymentIntent');
