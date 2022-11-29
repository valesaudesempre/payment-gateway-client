<?php

namespace ValeSaude\PaymentGatewayClient\Events;

use Throwable;
use ValeSaude\PaymentGatewayClient\Models\AbstractModel;
use ValeSaude\PaymentGatewayClient\Models\Webhook;

class WebhookProcessingFailed extends AbstractWebhookEvent
{
    private Throwable $error;

    public function __construct(Webhook $webhook, Throwable $error)
    {
        parent::__construct($webhook);
        $this->error = $error;
    }

    public function getError(): Throwable
    {
        return $this->error;
    }

    public function getSubject(): ?AbstractModel
    {
        return null;
    }
}
