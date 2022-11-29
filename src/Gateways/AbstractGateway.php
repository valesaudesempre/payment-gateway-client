<?php

namespace ValeSaude\PaymentGatewayClient\Gateways;

use ValeSaude\PaymentGatewayClient\Gateways\Contracts\GatewayInterface;
use ValeSaude\PaymentGatewayClient\Gateways\Enums\GatewayFeature;

abstract class AbstractGateway implements GatewayInterface
{
    public function getSupportedFeatures(): array
    {
        return GatewayFeature::cases();
    }

    public function isFeatureSupported(GatewayFeature $feature): bool
    {
        return in_array($feature, $this->getSupportedFeatures());
    }
}
