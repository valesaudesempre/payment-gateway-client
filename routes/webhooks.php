<?php

use Illuminate\Support\Facades\Route;
use ValeSaude\PaymentGatewayClient\Http\Controllers\WebhookController;

Route::post('webhooks/gateway/{gateway}', WebhookController::class)
    ->middleware(config('payment-gateway-client.webhook_route_middlewares'))
    ->name('webhooks.gateway');
