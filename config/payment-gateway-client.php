<?php

use ValeSaude\PaymentGatewayClient\Gateways\Iugu\IuguGateway;

return [
    'default_gateway' => 'iugu',

    'gateways' => [
        'iugu' => IuguGateway::class,
    ],
];
