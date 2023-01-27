<?php

namespace ValeSaude\PaymentGatewayClient\Contracts;

use ValeSaude\LaravelValueObjects\Money;
use ValeSaude\PaymentGatewayClient\Customer\CustomerDTO;
use ValeSaude\PaymentGatewayClient\Customer\PaymentMethodDTO;
use ValeSaude\PaymentGatewayClient\Gateways\Contracts\GatewayInterface;
use ValeSaude\PaymentGatewayClient\Invoice\InvoiceDTO;
use ValeSaude\PaymentGatewayClient\Models\Customer;
use ValeSaude\PaymentGatewayClient\Models\Invoice;
use ValeSaude\PaymentGatewayClient\Models\PaymentMethod;
use ValeSaude\PaymentGatewayClient\Models\Recipient;
use ValeSaude\PaymentGatewayClient\Recipient\RecipientDTO;

interface ClientInterface
{
    public function createCustomer(CustomerDTO $data): Customer;

    public function updateCustomer(Customer $customer, CustomerDTO $data): Customer;

    public function createPaymentMethod(
        Customer $customer,
        PaymentMethodDTO $data,
        bool $setAsDefault = true
    ): PaymentMethod;

    public function deletePaymentMethod(PaymentMethod $method): void;

    public function createInvoice(Customer $customer, InvoiceDTO $data, ?CustomerDTO $payer = null): Invoice;

    public function refreshInvoiceStatus(Invoice $invoice): Invoice;

    public function chargeInvoiceUsingPaymentMethod(
        Invoice $invoice,
        Customer $customer,
        ?PaymentMethod $method = null,
        int $installments = 1
    ): Invoice;

    public function chargeInvoiceUsingToken(Invoice $invoice, string $token, int $installments = 1): Invoice;

    public function refundInvoice(Invoice $invoice, ?Money $refundedAmount = null): Invoice;

    public function createRecipient(RecipientDTO $data): Recipient;

    public function getGateway(): GatewayInterface;
}
