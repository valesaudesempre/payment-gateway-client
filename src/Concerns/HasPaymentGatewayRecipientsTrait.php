<?php

namespace ValeSaude\PaymentGatewayClient\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use ValeSaude\PaymentGatewayClient\Models\Recipient;
use ValeSaude\PaymentGatewayClient\QueryBuilders\RecipientQueryBuilder;

/**
 * @mixin Model
 */
trait HasPaymentGatewayRecipientsTrait
{
    public function getDefaultGatewaySlug(): string
    {
        return config('payment-gateway-client.default_gateway');
    }

    /**
     * @return RecipientQueryBuilder|MorphMany
     */
    public function gatewayRecipients(): MorphMany
    {
        return $this->morphMany(Recipient::class, 'owner');
    }

    public function getGatewayRecipient(?string $gatewaySlug = null): ?Recipient
    {
        return $this
            ->gatewayRecipients()
            ->belongsToGateway($gatewaySlug ?? $this->getDefaultGatewaySlug())
            ->first();
    }

    public function getGatewayRecipientId(?string $gatewaySlug = null): ?string
    {
        return (string) $this->getGatewayRecipient($gatewaySlug)->gateway_id;
    }
}
