<?php

namespace ValeSaude\PaymentGatewayClient\Gateways\Contracts;

use Illuminate\Http\Request;
use ValeSaude\PaymentGatewayClient\Models\Webhook;

interface WebhookProcessorInterface
{
    public function authenticate(Request $request): bool;

    public function process(Webhook $webhook): bool;

    /**
     * @return class-string<WebhookEventHandlerInterface>[]
     */
    public function getEventHandlers(): array;
}
