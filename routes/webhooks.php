<?php

use Illuminate\Support\Facades\Route;
use ValeSaude\PaymentGatewayClient\Http\Controllers\WebhookController;

Route::post('webhooks/gateway/{gateway}', WebhookController::class)
    ->name('webhooks.gateway');
