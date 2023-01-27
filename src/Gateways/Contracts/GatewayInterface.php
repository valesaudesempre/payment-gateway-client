<?php

namespace ValeSaude\PaymentGatewayClient\Gateways\Contracts;

use ValeSaude\LaravelValueObjects\Money;
use ValeSaude\PaymentGatewayClient\Customer\CustomerDTO;
use ValeSaude\PaymentGatewayClient\Customer\GatewayPaymentMethodDTO;
use ValeSaude\PaymentGatewayClient\Customer\PaymentMethodDTO;
use ValeSaude\PaymentGatewayClient\Gateways\Enums\GatewayFeature;
use ValeSaude\PaymentGatewayClient\Invoice\GatewayInvoiceDTO;
use ValeSaude\PaymentGatewayClient\Invoice\InvoiceDTO;
use ValeSaude\PaymentGatewayClient\Recipient\GatewayRecipientDTO;
use ValeSaude\PaymentGatewayClient\Recipient\RecipientDTO;

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

    public function getInvoice(string $invoiceId): GatewayInvoiceDTO;

    public function chargeInvoiceUsingPaymentMethod(
        string $invoiceId,
        string $customerId,
        string $paymentMethodId,
        int $installments = 1
    ): void;

    public function chargeInvoiceUsingToken(string $invoiceId, string $token, int $installments = 1): void;

    public function refundInvoice(string $invoiceId, ?Money $refundValue = null): void;

    public function deletePaymentMethod(string $customerId, string $paymentMethodId): void;

    public function createRecipient(RecipientDTO $data): GatewayRecipientDTO;

    public function getGatewayIdentifier(): string;

    /**
     * @return GatewayFeature[]
     */
    public function getSupportedFeatures(): array;

    public function isFeatureSupported(GatewayFeature $feature): bool;
}
