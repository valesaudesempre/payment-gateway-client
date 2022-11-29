<?php

namespace ValeSaude\PaymentGatewayClient\Gateways\Exceptions;

class TransactionDeclinedException extends GatewayException
{
    public static function withLR(string $lrCode): self
    {
        return new self("Transaction declined with LR {$lrCode}.");
    }
}
