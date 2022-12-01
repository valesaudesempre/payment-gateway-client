<?php

namespace ValeSaude\PaymentGatewayClient\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use ValeSaude\PaymentGatewayClient\ValueObjects\Money;

class MoneyCast implements CastsAttributes
{
    /**
     * @param int|null             $value
     * @param array<string, mixed> $attributes
     */
    public function get($model, string $key, $value, array $attributes): ?Money
    {
        if ($value === null) {
            return null;
        }

        return new Money($value);
    }

    /**
     * @param Money|null           $value
     * @param array<string, mixed> $attributes
     */
    public function set($model, string $key, $value, array $attributes): ?int
    {
        if ($value === null) {
            return null;
        }

        return $value->getCents();
    }
}
