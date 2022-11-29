<?php

namespace ValeSaude\PaymentGatewayClient\Casts;

use ValeSaude\PaymentGatewayClient\ValueObjects\Contracts\StringableValueObjectInterface;

/**
 * @template-extends AbstractValueObjectCast<StringableValueObjectInterface>
 */
class StringableValueObjectCast extends AbstractValueObjectCast
{
    /**
     * @param string|null          $value
     * @param array<string, mixed> $attributes
     */
    public function get($model, string $key, $value, array $attributes): ?StringableValueObjectInterface
    {
        if (null === $value) {
            return null;
        }

        $class = $this->valueObjectClass;

        return new $class($value);
    }

    /**
     * @param StringableValueObjectInterface|null $value
     * @param array<string, mixed>                $attributes
     */
    public function set($model, string $key, $value, array $attributes): ?string
    {
        if (null === $value) {
            return null;
        }

        return (string) $value;
    }
}
