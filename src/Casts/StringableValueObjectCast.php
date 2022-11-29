<?php

namespace ValeSaude\PaymentGatewayClient\Casts;

use ValeSaude\PaymentGatewayClient\ValueObjects\Contracts\StringableValueObjectInterface;

/**
 * @template-extends AbstractValueObjectCast<StringableValueObjectInterface>
 */
class StringableValueObjectCast extends AbstractValueObjectCast
{
    /**
     * @param string               $value
     * @param array<string, mixed> $attributes
     */
    public function get($model, string $key, $value, array $attributes): StringableValueObjectInterface
    {
        $class = $this->valueObjectClass;

        return new $class($value);
    }

    /**
     * @param StringableValueObjectInterface $value
     * @param array<string, mixed>           $attributes
     */
    public function set($model, string $key, $value, array $attributes): string
    {
        return (string) $value;
    }
}
