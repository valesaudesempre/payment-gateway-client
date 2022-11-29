<?php

namespace ValeSaude\PaymentGatewayClient\ValueObjects;

abstract class AbstractValueObject
{
    public function equals(self $other): bool
    {
        return $this == $other;
    }

    public function notEquals(self $other): bool
    {
        return !$this->equals($other);
    }
}
