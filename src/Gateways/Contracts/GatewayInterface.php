<?php

namespace ValeSaude\PaymentGatewayClient\Gateways\Contracts;

use ValeSaude\PaymentGatewayClient\Customer\CustomerDTO;
use ValeSaude\PaymentGatewayClient\Gateways\Enums\GatewayFeature;

interface GatewayInterface
{
    public function createCustomer(CustomerDTO $data, string $externalReference): string;

    public function updateCustomer(string $id, CustomerDTO $data): void;

    public function getGatewayIdentifier(): string;

    /**
     * @return GatewayFeature[]
     */
    public function getSupportedFeatures(): array;

    public function isFeatureSupported(GatewayFeature $feature): bool;
}
