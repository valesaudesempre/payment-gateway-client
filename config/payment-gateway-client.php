<?php

use ValeSaude\PaymentGatewayClient\Gateways\Iugu\IuguGateway;
use ValeSaude\PaymentGatewayClient\Gateways\Iugu\Webhook\IuguWebhookProcessor;

return [
    'default_gateway' => 'iugu',

    'gateways' => [
        'iugu' => IuguGateway::class,
    ],

    'webhook_processors' => [
        'iugu' => IuguWebhookProcessor::class,
    ],

    'webhook_processing_queue' => 'default',
];
