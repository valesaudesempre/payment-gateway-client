<?php

namespace ValeSaude\PaymentGatewayClient\ValueObjects;

use Illuminate\Contracts\Database\Eloquent\Castable;
use InvalidArgumentException;
use ValeSaude\PaymentGatewayClient\Casts\StringableValueObjectCast;
use ValeSaude\PaymentGatewayClient\ValueObjects\Contracts\StringableValueObjectInterface;

class Email extends AbstractValueObject implements StringableValueObjectInterface, Castable
{
    private string $email;

    public function __construct(string $email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('The provided value is not a valid email.');
        }

        $this->email = $email;
    }

    public function __toString(): string
    {
        return $this->email;
    }

    /**
     * @param array<array-key, mixed> $arguments
     */
    public static function castUsing(array $arguments): StringableValueObjectCast
    {
        return new StringableValueObjectCast(static::class);
    }
}
