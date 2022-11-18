<?php

namespace ValeSaude\PaymentGatewayClient\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use ValeSaude\PaymentGatewayClient\ValueObjects\Contracts\StringableValueObjectInterface;

class StringableValueObjectCast implements CastsAttributes
{
    /**
     * @var class-string<StringableValueObjectInterface>
     */
    private string $valueObjectClass;

    /**
     * @param class-string<StringableValueObjectInterface> $valueObjectClass
     */
    public function __construct(string $valueObjectClass)
    {
        $this->valueObjectClass = $valueObjectClass;
    }

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
