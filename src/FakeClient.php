<?php

namespace ValeSaude\PaymentGatewayClient;

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Illuminate\Testing\Assert;
use ValeSaude\PaymentGatewayClient\Contracts\ClientInterface;
use ValeSaude\PaymentGatewayClient\Customer\CustomerDTO;
use ValeSaude\PaymentGatewayClient\Customer\GatewayPaymentMethodDTO;
use ValeSaude\PaymentGatewayClient\Customer\PaymentMethodDTO;
use ValeSaude\PaymentGatewayClient\Gateways\Contracts\GatewayInterface;
use ValeSaude\PaymentGatewayClient\Gateways\Fake\FakeGateway;
use ValeSaude\PaymentGatewayClient\Invoice\Builders\InvoiceBuilder;
use ValeSaude\PaymentGatewayClient\Invoice\Enums\InvoicePaymentMethod;
use ValeSaude\PaymentGatewayClient\Invoice\Enums\InvoiceStatus;
use ValeSaude\PaymentGatewayClient\Invoice\GatewayInvoiceDTO;
use ValeSaude\PaymentGatewayClient\Invoice\InvoiceDTO;
use ValeSaude\PaymentGatewayClient\Invoice\InvoiceItemDTO;
use ValeSaude\PaymentGatewayClient\Models\Customer;
use ValeSaude\PaymentGatewayClient\Models\Invoice;
use ValeSaude\PaymentGatewayClient\Models\PaymentMethod;
use ValeSaude\PaymentGatewayClient\ValueObjects\Money;

class FakeClient implements ClientInterface
{
    private string $originalGatewaySlug;
    private GatewayInterface $gateway;
    private ClientInterface $client;

    public function __construct(string $originalGatewaySlug)
    {
        $this->originalGatewaySlug = $originalGatewaySlug;
        $this->gateway = new FakeGateway($this->originalGatewaySlug);
        $this->client = new Client($this->gateway);
    }

    /**
     * @codeCoverageIgnore
     */
    public function createCustomer(CustomerDTO $data): Customer
    {
        return $this->client->createCustomer($data);
    }

    /**
     * @codeCoverageIgnore
     */
    public function updateCustomer(Customer $customer, CustomerDTO $data): Customer
    {
        return $this->client->updateCustomer($customer, $data);
    }

    /**
     * @codeCoverageIgnore
     */
    public function createPaymentMethod(Customer $customer, PaymentMethodDTO $data, bool $setAsDefault = true): PaymentMethod
    {
        return $this->client->createPaymentMethod($customer, $data, $setAsDefault);
    }

    /**
     * @codeCoverageIgnore
     */
    public function createInvoice(Customer $customer, InvoiceDTO $data, ?CustomerDTO $payer = null): Invoice
    {
        return $this->client->createInvoice($customer, $data, $payer);
    }

    public function refreshInvoiceStatus(Invoice $invoice): Invoice
    {
        return $this->client->refreshInvoiceStatus($invoice);
    }

    /**
     * @codeCoverageIgnore
     */
    public function chargeInvoiceUsingPaymentMethod(Invoice $invoice, Customer $customer, ?PaymentMethod $method = null): Invoice
    {
        return $this->client->chargeInvoiceUsingPaymentMethod($invoice, $customer, $method);
    }

    /**
     * @codeCoverageIgnore
     */
    public function chargeInvoiceUsingToken(Invoice $invoice, string $token): Invoice
    {
        return $this->client->chargeInvoiceUsingToken($invoice, $token);
    }

    /**
     * @codeCoverageIgnore
     */
    public function getGateway(): GatewayInterface
    {
        return $this->gateway;
    }

    /**
     * @param callable(CustomerDTO $data=, string $externalReference=, string $customerId=): bool $expectation
     */
    public function assertCustomerCreated(?callable $expectation = null): void
    {
        $wasCreated = false;

        if ($expectation === null) {
            Assert::assertNotEmpty(
                $this->gateway->getCustomers(),
                'Failed asserting that any customer was created.'
            );

            return;
        }

        foreach ($this->gateway->getCustomers() as $customerId => $params) {
            if (true === $expectation($params['data'], $params['external_reference'], $customerId)) {
                $wasCreated = true;

                break;
            }
        }

        Assert::assertTrue($wasCreated, 'Failed asserting that a given customer was created.');
    }

    public function assertCustomerNotCreated(): void
    {
        Assert::assertEmpty(
            $this->gateway->getCustomers(),
            'Failed asserting that no customer was created.'
        );
    }

    /**
     * @param callable(CustomerDTO $data=, CustomerDTO $original=, string $externalReference=, string $customerId=): bool $expectation
     */
    public function assertCustomerUpdated(?callable $expectation = null): void
    {
        $wasUpdated = false;

        if (!$expectation) {
            $expectation = static function (CustomerDTO $data, CustomerDTO $original) {
                return $data != $original;
            };
        }

        foreach ($this->gateway->getCustomers() as $customerId => $params) {
            if (true === $expectation($params['data'], $params['original'], $params['external_reference'], $customerId)) {
                $wasUpdated = true;

                break;
            }
        }

        Assert::assertTrue($wasUpdated, 'Failed asserting that a given customer was updated.');
    }

    /**
     * @param callable(GatewayPaymentMethodDTO $data=, bool $isDefault=, string $customerId=): bool $expectation
     */
    public function assertPaymentMethodCreated(?callable $expectation = null): void
    {
        $wasCreated = false;

        if ($expectation === null) {
            Assert::assertNotEmpty(
                $this->gateway->getPaymentMethods(),
                'Failed asserting that any payment method was created.'
            );

            return;
        }

        foreach ($this->gateway->getPaymentMethods() as $customerId => $paymentMethods) {
            foreach ($paymentMethods as $params) {
                if (true === $expectation($params['data'], $params['is_default'], $customerId)) {
                    $wasCreated = true;

                    break;
                }
            }
        }

        Assert::assertTrue($wasCreated, 'Failed asserting that a given payment method was created.');
    }

    public function assertPaymentMethodNotCreated(): void
    {
        Assert::assertEmpty(
            $this->gateway->getPaymentMethods(),
            'Failed asserting that no payment method was created.'
        );
    }

    /**
     * @param callable(GatewayInvoiceDTO $data=, CustomerDTO $payer=, string $externalReference=, string $customerId=): bool $expectation
     */
    public function assertInvoiceCreated(?callable $expectation = null): void
    {
        $wasCreated = false;

        if ($expectation === null) {
            Assert::assertNotEmpty(
                $this->gateway->getInvoices(),
                'Failed asserting that any invoice was created.'
            );

            return;
        }

        foreach ($this->gateway->getInvoices() as $customerId => $invoices) {
            foreach ($invoices as $params) {
                if (true === $expectation($params['data'], $params['payer'], $params['external_reference'], $customerId)) {
                    $wasCreated = true;

                    break;
                }
            }
        }

        Assert::assertTrue($wasCreated, 'Failed asserting that a given invoice was created.');
    }

    public function assertInvoiceNotCreated(): void
    {
        Assert::assertEmpty(
            $this->gateway->getInvoices(),
            'Failed asserting that no invoice was created.'
        );
    }

    /**
     * @param callable(GatewayInvoiceDTO $data=, string $externalReference=, string $customerId=, string|null $token=, string|null $paymentMethodId=): bool $expectation
     */
    public function assertInvoicePaid(?callable $expectation = null): void
    {
        $wasPaid = false;

        $paidInvoices = array_filter(
            $this->gateway->getInvoices(),
            static function (array $invoices) {
                return array_filter(
                    $invoices,
                    static fn (array $params) => $params['data']->status->equals(InvoiceStatus::PAID())
                );
            }
        );

        if ($expectation === null) {
            Assert::assertNotEmpty(
                $paidInvoices,
                'Failed asserting that any invoice was paid.'
            );

            return;
        }

        foreach ($paidInvoices as $customerId => $invoices) {
            foreach ($invoices as $params) {
                if (true === $expectation($params['data'], $params['external_reference'], $customerId, $params['token'], $params['payment_method_id'])) {
                    $wasPaid = true;

                    break;
                }
            }
        }

        Assert::assertTrue($wasPaid, 'Failed asserting that a given invoice was paid.');
    }

    public function assertInvoiceNotPaid(): void
    {
        $paidInvoices = array_filter(
            $this->gateway->getInvoices(),
            static function (array $invoices) {
                return array_filter(
                    $invoices,
                    static fn (array $params) => $params['data']->status->equals(InvoiceStatus::PAID())
                );
            }
        );

        Assert::assertEmpty(
            $paidInvoices,
            'Failed asserting that no invoice was paid.'
        );
    }

    public function mockExistingCustomer(?CustomerDTO $data = null): Customer
    {
        if (!$data) {
            $data = Customer
                ::factory()
                ->make()
                ->toCustomerDTO();
        }

        return $this->createCustomer($data);
    }

    public function mockExistingPaymentMethod(?Customer $customer = null, ?PaymentMethodDTO $data = null): PaymentMethod
    {
        if (!$customer) {
            $customer = $this->mockExistingCustomer();
        }

        if (!$data) {
            $data = new PaymentMethodDTO(
                'Some payment method',
                Str::uuid()
            );
        }

        return $this->createPaymentMethod($customer, $data);
    }

    public function mockExistingInvoice(?Customer $customer = null, ?InvoiceDTO $data = null): Invoice
    {
        if (!$customer) {
            $customer = $this->mockExistingCustomer();
        }

        if (!$data) {
            $data = InvoiceBuilder::make()
                ->setDueDate(CarbonImmutable::now()->addDay())
                ->setAvailablePaymentMethods(...InvoicePaymentMethod::cases())
                ->setMaxInstallments(12)
                ->addItem(new InvoiceItemDTO(new Money(1234), 1, 'Some item'))
                ->get();
        }

        return $this->createInvoice($customer, $data);
    }
}
