<?php

namespace ValeSaude\PaymentGatewayClient\ValueObjects;

use InvalidArgumentException;

class PositiveInteger extends AbstractValueObject
{
    private int $value;

    public function __construct(int $value)
    {
        if ($value <= 0) {
            throw new InvalidArgumentException('The value must be positive.');
        }

        $this->value = $value;
    }

    public function getValue(): int
    {
        return $this->value;
    }
}
