<?php

namespace ValeSaude\PaymentGatewayClient\ValueObjects;

use Illuminate\Contracts\Database\Eloquent\Castable;
use InvalidArgumentException;
use ValeSaude\PaymentGatewayClient\Casts\StringableValueObjectCast;
use ValeSaude\PaymentGatewayClient\Validators\ZipCodeValidator;
use ValeSaude\PaymentGatewayClient\ValueObjects\Contracts\StringableValueObjectInterface;

class ZipCode extends AbstractValueObject implements StringableValueObjectInterface, Castable
{
    private string $zipCode;

    public function __construct(string $zipCode)
    {
        $validator = new ZipCodeValidator();

        if (!$validator->validate($zipCode)) {
            throw new InvalidArgumentException('The provided value is not a valid ZipCode.');
        }

        $this->zipCode = $validator->sanitize($zipCode);
    }

    public function __toString(): string
    {
        return $this->zipCode;
    }

    /**
     * @param array<array-key, mixed> $arguments
     */
    public static function castUsing(array $arguments): StringableValueObjectCast
    {
        return new StringableValueObjectCast(static::class);
    }
}
