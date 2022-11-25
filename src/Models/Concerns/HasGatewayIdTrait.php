<?php

namespace ValeSaude\PaymentGatewayClient\Models\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string|null $gateway_id
 * @property string      $gateway_slug
 *
 * @mixin Model
 */
trait HasGatewayIdTrait
{
    /**
     * @return static
     */
    public static function findUsingGatewayId(string $gatewaySlug, string $gatewayId): ?self
    {
        return self
            ::query()
            ->where('gateway_slug', $gatewaySlug)
            ->where('gateway_id', $gatewayId)
            ->first();
    }
}
