<?php

namespace ValeSaude\PaymentGatewayClient\Gateways\Exceptions;

class InvalidPaymentTokenException extends GatewayException
{
    public static function invalidToken(): self
    {
        return new self('Invalid payment token.');
    }

    public static function tokenAlreadyUsed(): self
    {
        return new self('Token already used.');
    }
}
