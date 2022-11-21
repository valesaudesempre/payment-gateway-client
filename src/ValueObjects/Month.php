<?php

namespace ValeSaude\PaymentGatewayClient\ValueObjects;

use InvalidArgumentException;

class Month extends PositiveInteger
{
    public function __construct(int $value)
    {
        parent::__construct($value);

        if ($this->getValue() > 12) {
            throw new InvalidArgumentException('The value must be between 1 and 12.');
        }
    }
}
