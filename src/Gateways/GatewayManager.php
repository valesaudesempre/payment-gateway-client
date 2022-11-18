<?php

namespace ValeSaude\PaymentGatewayClient\Gateways;

use Illuminate\Contracts\Container\BindingResolutionException;
use ValeSaude\PaymentGatewayClient\Gateways\Contracts\GatewayInterface;

class GatewayManager
{
    /**
     * @throws BindingResolutionException
     */
    public static function resolve(string $slug): GatewayInterface
    {
        $class = config("payment-gateway-client.gateways.{$slug}");

        if (!isset($class)) {
            throw new BindingResolutionException("Unable to resolve gateway identified by \"{$slug}\".");
        }

        return app($class);
    }
}
