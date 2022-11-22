<?php

namespace ValeSaude\PaymentGatewayClient\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use ValeSaude\PaymentGatewayClient\ValueObjects\Money;

class MoneyCast implements CastsAttributes
{
    /**
     * @param int                  $value
     * @param array<string, mixed> $attributes
     */
    public function get($model, string $key, $value, array $attributes): Money
    {
        return new Money($value);
    }

    /**
     * @param Money                $value
     * @param array<string, mixed> $attributes
     */
    public function set($model, string $key, $value, array $attributes): int
    {
        return $value->getCents();
    }
}
