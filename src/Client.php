<?php

namespace ValeSaude\PaymentGatewayClient;

use InvalidArgumentException;
use ValeSaude\PaymentGatewayClient\Contracts\ClientInterface;
use ValeSaude\PaymentGatewayClient\Customer\CustomerDTO;
use ValeSaude\PaymentGatewayClient\Customer\PaymentMethodDTO;
use ValeSaude\PaymentGatewayClient\Exceptions\UnsupportedFeatureException;
use ValeSaude\PaymentGatewayClient\Gateways\Contracts\GatewayInterface;
use ValeSaude\PaymentGatewayClient\Gateways\Enums\GatewayFeature;
use ValeSaude\PaymentGatewayClient\Invoice\Collections\GatewayInvoiceItemDTOCollection;
use ValeSaude\PaymentGatewayClient\Invoice\Enums\InvoiceStatus;
use ValeSaude\PaymentGatewayClient\Invoice\GatewayInvoiceItemDTO;
use ValeSaude\PaymentGatewayClient\Invoice\InvoiceDTO;
use ValeSaude\PaymentGatewayClient\Invoice\InvoiceItemDTO;
use ValeSaude\PaymentGatewayClient\Models\Customer;
use ValeSaude\PaymentGatewayClient\Models\Invoice;
use ValeSaude\PaymentGatewayClient\Models\PaymentMethod;

class Client implements ClientInterface
{
    private GatewayInterface $gateway;

    public function __construct(GatewayInterface $gateway)
    {
        $this->gateway = $gateway;
    }

    public function createCustomer(CustomerDTO $data): Customer
    {
        $customer = Customer::fromCustomerDTO($data);
        $customer->gateway_slug = $this->gateway->getGatewayIdentifier();

        if ($this->gateway->isFeatureSupported(GatewayFeature::CUSTOMER())) {
            $id = $this->gateway->createCustomer($data, $customer->id);
            $customer->gateway_id = $id;
        }

        return tap($customer)->save();
    }

    public function updateCustomer(Customer $customer, CustomerDTO $data): Customer
    {
        if ($this->gateway->isFeatureSupported(GatewayFeature::CUSTOMER())) {
            // @phpstan-ignore-next-line
            $this->gateway->updateCustomer($customer->gateway_id, $data);
        }

        return $customer->updateUsingCustomerDTO($data);
    }

    public function createPaymentMethod(
        Customer $customer,
        PaymentMethodDTO $data,
        bool $setAsDefault = true
    ): PaymentMethod {
        $this->ensureFeatureIsSupported(GatewayFeature::PAYMENT_METHOD());

        $paymentMethod = PaymentMethod::fromPaymentMethodDTO($data);
        // @phpstan-ignore-next-line
        $gatewayPaymentMethod = $this->gateway->createPaymentMethod($customer->gateway_id, $data, $setAsDefault);

        $paymentMethod->card = $gatewayPaymentMethod->card;
        $paymentMethod->gateway_id = $gatewayPaymentMethod->id;
        $paymentMethod->gateway_slug = $this->gateway->getGatewayIdentifier();

        $customer->paymentMethods()->save($paymentMethod);

        if ($setAsDefault) {
            $paymentMethod->setAsDefault();
        }

        return $paymentMethod;
    }

    public function createInvoice(Customer $customer, InvoiceDTO $data, ?CustomerDTO $payer = null): Invoice
    {
        if (isset($data->splits) && count($data->splits)) {
            $this->ensureFeatureIsSupported(GatewayFeature::INVOICE_SPLIT());
        }

        $invoice = Invoice::fromInvoiceDTO($data);
        $items = null;

        if ($this->gateway->isFeatureSupported(GatewayFeature::INVOICE())) {
            $gatewayInvoice = $this->gateway->createInvoice(
                $customer->gateway_id,
                $data,
                $payer ?? $customer->toCustomerDTO(),
                $invoice->id
            );

            $invoice->url = $gatewayInvoice->url;
            // @phpstan-ignore-next-line
            $invoice->due_date = $gatewayInvoice->dueDate;
            $invoice->status = $gatewayInvoice->status;
            $invoice->gateway_id = $gatewayInvoice->id;
            $invoice->bank_slip_code = $gatewayInvoice->bankSlipCode;
            $invoice->pix_code = $gatewayInvoice->pixCode;
            $items = $gatewayInvoice->items;
        } else {
            // @phpstan-ignore-next-line
            $invoice->due_date = $data->dueDate;
            $invoice->status = InvoiceStatus::PENDING();
            $items = new GatewayInvoiceItemDTOCollection(
                $data->items->map(static fn (InvoiceItemDTO $item) => GatewayInvoiceItemDTO::fromInvoiceItemDTO($item))
            );
        }

        $invoice->gateway_slug = $this->gateway->getGatewayIdentifier();

        $customer->invoices()->save($invoice);

        $invoice->items()->createMany(
            $items->map(fn (GatewayInvoiceItemDTO $item) => [
                'gateway_id' => $item->id,
                'gateway_slug' => $invoice->gateway_slug,
                'price' => $item->price,
                'quantity' => $item->quantity,
                'description' => $item->description,
            ])
        );

        return $invoice;
    }

    public function chargeInvoiceUsingPaymentMethod(
        Invoice $invoice,
        Customer $customer,
        ?PaymentMethod $method = null
    ): Invoice {
        if (!$method) {
            $defaultPaymentMethod = $customer->getDefaultPaymentMethod();

            if (!$defaultPaymentMethod) {
                throw new InvalidArgumentException('The customer does not have a default payment method.');
            }

            $method = $defaultPaymentMethod;
        }

        // @phpstan-ignore-next-line
        $this->gateway->chargeInvoiceUsingPaymentMethod($invoice->gateway_id, $customer->gateway_id, $method->gateway_id);

        $invoice->markAsPaid();

        return $invoice;
    }

    public function chargeInvoiceUsingToken(Invoice $invoice, string $token): Invoice
    {
        // @phpstan-ignore-next-line
        $this->gateway->chargeInvoiceUsingToken($invoice->gateway_id, $token);

        $invoice->markAsPaid();

        return $invoice;
    }

    /**
     * @throws UnsupportedFeatureException
     */
    public function ensureFeatureIsSupported(GatewayFeature $feature): void
    {
        if (!$this->gateway->isFeatureSupported($feature)) {
            throw UnsupportedFeatureException::withFeatureAndGateway(
                $feature,
                $this->gateway->getGatewayIdentifier()
            );
        }
    }

    public function getGateway(): GatewayInterface
    {
        return $this->gateway;
    }
}
