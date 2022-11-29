<?php

namespace ValeSaude\PaymentGatewayClient\Gateways\Contracts;

use ValeSaude\PaymentGatewayClient\Customer\CustomerDTO;

interface GatewayInterface
{
    public function createCustomer(CustomerDTO $data, string $internalId): string;

    public function getGatewayIdentifier(): string;
}
