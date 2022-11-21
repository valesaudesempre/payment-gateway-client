<?php

namespace ValeSaude\PaymentGatewayClient\Exceptions;

use RuntimeException;
use ValeSaude\PaymentGatewayClient\Gateways\Enums\GatewayFeature;

class UnsupportedFeatureException extends RuntimeException
{
    public static function withFeatureAndGateway(GatewayFeature $feature, string $gateway): self
    {
        return new self("The gateway \"{$gateway}\" does not support \"{$feature->label}\" feature.");
    }
}
