<?php

namespace ValeSaude\PaymentGatewayClient\Gateways\Contracts;

use ValeSaude\PaymentGatewayClient\Customer\CustomerDTO;
use ValeSaude\PaymentGatewayClient\Customer\GatewayPaymentMethodDTO;
use ValeSaude\PaymentGatewayClient\Customer\PaymentMethodDTO;
use ValeSaude\PaymentGatewayClient\Gateways\Enums\GatewayFeature;
use ValeSaude\PaymentGatewayClient\Invoice\GatewayInvoiceDTO;
use ValeSaude\PaymentGatewayClient\Invoice\InvoiceDTO;

interface GatewayInterface
{
    public function createCustomer(CustomerDTO $data, string $externalReference): string;

    public function updateCustomer(string $id, CustomerDTO $data): void;

    public function createPaymentMethod(
        string $customerId,
        PaymentMethodDTO $data,
        bool $setAsDefault = true
    ): GatewayPaymentMethodDTO;

    public function createInvoice(
        ?string $customerId,
        InvoiceDTO $data,
        CustomerDTO $payer,
        string $externalReference
    ): GatewayInvoiceDTO;

    public function getGatewayIdentifier(): string;

    /**
     * @return GatewayFeature[]
     */
    public function getSupportedFeatures(): array;

    public function isFeatureSupported(GatewayFeature $feature): bool;
}
