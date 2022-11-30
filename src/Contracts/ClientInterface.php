<?php

namespace ValeSaude\PaymentGatewayClient\Contracts;

use ValeSaude\PaymentGatewayClient\Customer\CustomerDTO;
use ValeSaude\PaymentGatewayClient\Customer\PaymentMethodDTO;
use ValeSaude\PaymentGatewayClient\Gateways\Contracts\GatewayInterface;
use ValeSaude\PaymentGatewayClient\Invoice\InvoiceDTO;
use ValeSaude\PaymentGatewayClient\Models\Customer;
use ValeSaude\PaymentGatewayClient\Models\Invoice;
use ValeSaude\PaymentGatewayClient\Models\PaymentMethod;

interface ClientInterface
{
    public function createCustomer(CustomerDTO $data): Customer;

    public function updateCustomer(Customer $customer, CustomerDTO $data): Customer;

    public function createPaymentMethod(
        Customer $customer,
        PaymentMethodDTO $data,
        bool $setAsDefault = true
    ): PaymentMethod;

    public function createInvoice(Customer $customer, InvoiceDTO $data, ?CustomerDTO $payer = null): Invoice;

    public function chargeInvoiceUsingPaymentMethod(
        Invoice $invoice,
        Customer $customer,
        ?PaymentMethod $method = null
    ): Invoice;

    public function chargeInvoiceUsingToken(Invoice $invoice, string $token): Invoice;

    public function getGateway(): GatewayInterface;
}
