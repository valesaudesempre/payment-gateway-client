<?php

namespace ValeSaude\PaymentGatewayClient\Gateways\Exceptions;

use ValeSaude\PaymentGatewayClient\Models\Webhook;

class WebhookException extends GatewayException
{
    private ?Webhook $webhook;

    public function getWebhook(): ?Webhook
    {
        return $this->webhook;
    }

    /**
     * @return static
     */
    public function setWebhook(Webhook $webhook): self
    {
        $this->webhook = $webhook;

        return $this;
    }

    /**
     * @return static
     */
    public static function withWebhookAndReason(Webhook $webhook, string $reason): self
    {
        // @phpstan-ignore-next-line
        return (new static($reason))
            ->setWebhook($webhook);
    }
}
