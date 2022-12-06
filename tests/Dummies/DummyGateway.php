<?php

namespace ValeSaude\PaymentGatewayClient\Tests\Dummies;

use BadMethodCallException;
use ValeSaude\PaymentGatewayClient\Customer\CustomerDTO;
use ValeSaude\PaymentGatewayClient\Customer\GatewayPaymentMethodDTO;
use ValeSaude\PaymentGatewayClient\Customer\PaymentMethodDTO;
use ValeSaude\PaymentGatewayClient\Gateways\Contracts\GatewayInterface;
use ValeSaude\PaymentGatewayClient\Gateways\Enums\GatewayFeature;
use ValeSaude\PaymentGatewayClient\Invoice\GatewayInvoiceDTO;
use ValeSaude\PaymentGatewayClient\Invoice\InvoiceDTO;

class DummyGateway implements GatewayInterface
{
    public function createCustomer(CustomerDTO $data, string $externalReference): string
    {
        throw new BadMethodCallException('Not implemented.');
    }

    public function updateCustomer(string $id, CustomerDTO $data): void
    {
        throw new BadMethodCallException('Not implemented.');
    }

    public function createPaymentMethod(string $customerId, PaymentMethodDTO $data, bool $setAsDefault = true): GatewayPaymentMethodDTO
    {
        throw new BadMethodCallException('Not implemented.');
    }

    public function createInvoice(?string $customerId, InvoiceDTO $data, CustomerDTO $payer, string $externalReference): GatewayInvoiceDTO
    {
        throw new BadMethodCallException('Not implemented.');
    }

    public function getInvoice(string $invoiceId): GatewayInvoiceDTO
    {
        throw new BadMethodCallException('Not implemented.');
    }

    public function chargeInvoiceUsingPaymentMethod(
        string $invoiceId,
        string $customerId,
        string $paymentMethodId,
        int $installments = 1
    ): void {
        throw new BadMethodCallException('Not implemented.');
    }

    public function chargeInvoiceUsingToken(string $invoiceId, string $token, int $installments = 1): void
    {
        throw new BadMethodCallException('Not implemented.');
    }

    public function getGatewayIdentifier(): string
    {
        throw new BadMethodCallException('Not implemented.');
    }

    public function getSupportedFeatures(): array
    {
        throw new BadMethodCallException('Not implemented.');
    }

    public function isFeatureSupported(GatewayFeature $feature): bool
    {
        throw new BadMethodCallException('Not implemented.');
    }
}
