<?php

namespace ValeSaude\PaymentGatewayClient\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * @template T
 */
abstract class AbstractValueObjectCast implements CastsAttributes
{
    /**
     * @var class-string<T>
     */
    protected string $valueObjectClass;

    /**
     * @param class-string<T> $valueObjectClass
     */
    public function __construct(string $valueObjectClass)
    {
        $this->valueObjectClass = $valueObjectClass;
    }
}
