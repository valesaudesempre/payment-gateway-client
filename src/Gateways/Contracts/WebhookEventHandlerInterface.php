<?php

namespace ValeSaude\PaymentGatewayClient\Gateways\Contracts;

use ValeSaude\PaymentGatewayClient\Models\Webhook;

interface WebhookEventHandlerInterface
{
    public function shouldHandle(Webhook $webhook): bool;

    public function handle(Webhook $webhook): void;
}
