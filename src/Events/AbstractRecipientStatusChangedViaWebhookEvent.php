<?php

namespace ValeSaude\PaymentGatewayClient\Events;

use ValeSaude\PaymentGatewayClient\Models\Recipient;
use ValeSaude\PaymentGatewayClient\Models\Webhook;

abstract class AbstractRecipientStatusChangedViaWebhookEvent extends AbstractWebhookEvent
{
    private Recipient $recipient;

    public function __construct(Webhook $webhook, Recipient $recipient)
    {
        parent::__construct($webhook);
        $this->recipient = $recipient;
    }

    public function getSubject(): Recipient
    {
        return $this->recipient;
    }
}
