<?php

namespace ValeSaude\PaymentGatewayClient\Gateways;

use Illuminate\Contracts\Container\BindingResolutionException;
use ValeSaude\PaymentGatewayClient\Gateways\Contracts\GatewayInterface;
use ValeSaude\PaymentGatewayClient\Gateways\Contracts\WebhookProcessorInterface;

class GatewayManager
{
    /**
     * @throws BindingResolutionException
     */
    public static function resolveGateway(string $slug): GatewayInterface
    {
        $class = config("payment-gateway-client.gateways.{$slug}");

        if (!isset($class)) {
            throw new BindingResolutionException("Unable to resolve gateway identified by \"{$slug}\".");
        }

        return resolve($class);
    }

    public static function resolveWebhookProcessor(string $slug): WebhookProcessorInterface
    {
        $class = config("payment-gateway-client.webhook_processors.{$slug}");

        if (!isset($class)) {
            throw new BindingResolutionException("Unable to resolve webhook processor identified by \"{$slug}\".");
        }

        return resolve($class);
    }
}
