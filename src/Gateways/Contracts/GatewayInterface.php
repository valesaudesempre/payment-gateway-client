<?php

namespace ValeSaude\PaymentGatewayClient\Gateways\Contracts;

use ValeSaude\PaymentGatewayClient\Customer\CustomerDTO;

interface GatewayInterface
{
    public function createCustomer(CustomerDTO $data, string $externalReference): string;

    public function updateCustomer($id, CustomerDTO $data): void;

    public function getGatewayIdentifier(): string;
}
