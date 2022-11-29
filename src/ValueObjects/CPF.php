<?php

namespace ValeSaude\PaymentGatewayClient\ValueObjects;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;
use ValeSaude\PaymentGatewayClient\Casts\StringableValueObjectCast;
use ValeSaude\PaymentGatewayClient\Validators\CPFValidator;
use ValeSaude\PaymentGatewayClient\ValueObjects\Contracts\StringableValueObjectInterface;

class CPF extends AbstractValueObject implements StringableValueObjectInterface, Castable
{
    private string $cpf;

    public function __construct(string $cpf)
    {
        $validator = new CPFValidator();

        if (!$validator->validate($cpf)) {
            throw new InvalidArgumentException('The provided value is not a valid CPF.');
        }

        $this->cpf = $validator->sanitize($cpf);
    }

    public function __toString(): string
    {
        return $this->cpf;
    }

    /**
     * @param array<array-key, mixed> $arguments
     */
    public static function castUsing(array $arguments): CastsAttributes
    {
        return new StringableValueObjectCast(static::class);
    }
}
