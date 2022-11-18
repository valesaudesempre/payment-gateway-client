<?php

namespace ValeSaude\PaymentGatewayClient\ValueObjects\Contracts;

use ValeSaude\PaymentGatewayClient\ValueObjects\AbstractValueObject;

/**
 * @mixin AbstractValueObject
 */
interface StringableValueObjectInterface
{
    public function __toString(): string;
}
