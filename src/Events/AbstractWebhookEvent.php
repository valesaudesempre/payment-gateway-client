<?php

namespace ValeSaude\PaymentGatewayClient\Events;

use ValeSaude\PaymentGatewayClient\Models\AbstractModel;
use ValeSaude\PaymentGatewayClient\Models\Webhook;

abstract class AbstractWebhookEvent
{
    private Webhook $webhook;

    public function __construct(Webhook $webhook)
    {
        $this->webhook = $webhook;
    }

    abstract public function getSubject(): ?AbstractModel;

    public function getWebhook(): Webhook
    {
        return $this->webhook;
    }

    public function getEventName(): ?string
    {
        return $this->webhook->event;
    }
}
