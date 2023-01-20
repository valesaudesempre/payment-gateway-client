<?php

use Illuminate\Support\Str;
use PHPUnit\Framework\AssertionFailedError;
use ValeSaude\LaravelValueObjects\Document;
use ValeSaude\LaravelValueObjects\Enums\DocumentType;
use ValeSaude\PaymentGatewayClient\Customer\CustomerDTO;
use ValeSaude\PaymentGatewayClient\Customer\GatewayPaymentMethodDTO;
use ValeSaude\PaymentGatewayClient\FakeClient;
use ValeSaude\PaymentGatewayClient\Invoice\GatewayInvoiceDTO;
use ValeSaude\PaymentGatewayClient\Recipient\RecipientDTO;
use ValeSaude\PaymentGatewayClient\Tests\Concerns\HasCustomerHelperMethodsTrait;
use ValeSaude\PaymentGatewayClient\Tests\Concerns\HasInvoiceHelperMethodsTrait;
use ValeSaude\PaymentGatewayClient\Tests\Concerns\HasRecipientHelperMethodsTrait;

uses(
    HasCustomerHelperMethodsTrait::class,
    HasInvoiceHelperMethodsTrait::class,
    HasRecipientHelperMethodsTrait::class,
);

beforeEach(function () {
    $this->sut = new FakeClient('some-gateway-slug');
});

test('assertCustomerCreated throws AssertionFailedException when expectation is not provided and no customer was created', function () {
    // then
    $this->expectExceptionObject(new AssertionFailedError('Failed asserting that any customer was created.'));

    // when
    $this->sut->assertCustomerCreated();
});

test('assertCustomerCreated throws when expectation does not match any created customer', function () {
    // given
    $this->sut->createCustomer($this->createCustomerDTO());
    $this->sut->createCustomer($this->createCustomerDTO());

    // then
    $this->expectExceptionObject(new AssertionFailedError('Failed asserting that a given customer was created.'));

    // when
    $this->sut->assertCustomerCreated(function (CustomerDTO $data) {
        return $data->document->equals(new Document($this->faker->cnpj(false), DocumentType::CNPJ()));
    });
});

test('assertCustomerCreated correctly asserts created customers', function () {
    // given
    $this->sut->createCustomer($this->createCustomerDTO());
    $customer = $this->sut->createCustomer($this->createCustomerDTO());

    // when
    $this->sut->assertCustomerCreated();
    $this->sut->assertCustomerCreated(static function (CustomerDTO $data) use ($customer) {
        return $data->document->equals($customer->document);
    });
});

test('assertCustomerNotCreated throws when expectation at least one customer was created', function () {
    // given
    $this->sut->createCustomer($this->createCustomerDTO());

    // then
    $this->expectExceptionObject(new AssertionFailedError('Failed asserting that no customer was created.'));

    // when
    $this->sut->assertCustomerNotCreated();
});

test('assertCustomerNotCreated correctly asserts no customers created', function () {
    // when
    $this->sut->assertCustomerNotCreated();
});

test('assertCustomerUpdated throws AssertionFailedException when expectation is not provided and no customer was updated', function () {
    // then
    $this->expectExceptionObject(new AssertionFailedError('Failed asserting that a given customer was updated.'));

    // when
    $this->sut->assertCustomerUpdated();
});

test('assertCustomerUpdated throws when expectation does not match any updated customer', function () {
    // given
    $customer = $this->sut->mockExistingCustomer();
    $this->sut->updateCustomer($customer, $this->createCustomerDTO());

    // then
    $this->expectExceptionObject(new AssertionFailedError('Failed asserting that a given customer was updated.'));

    // when
    $this->sut->assertCustomerUpdated(function (CustomerDTO $data) {
        return $data->document->equals(new Document($this->faker->cnpj(false), DocumentType::CNPJ()));
    });
});

test('assertCustomerUpdated correctly asserts updated customers', function () {
    // given
    $this->sut->mockExistingCustomer();
    $customer = $this->sut->mockExistingCustomer();
    $this->sut->updateCustomer($customer, $this->createCustomerDTO());

    // when
    $this->sut->assertCustomerUpdated();
    $this->sut->assertCustomerUpdated(static function (CustomerDTO $data) use ($customer) {
        return $data->document->equals($customer->document);
    });
});

test('assertPaymentMethodCreated throws AssertionFailedException when expectation is not provided and no payment method was created', function () {
    // then
    $this->expectExceptionObject(new AssertionFailedError('Failed asserting that any payment method was created.'));

    // when
    $this->sut->assertPaymentMethodCreated();
});

test('assertPaymentMethodCreated throws when expectation does not match any created payment method', function () {
    // given
    $customer = $this->sut->mockExistingCustomer();
    $this->sut->createPaymentMethod($customer, $this->createPaymentMethodDTO());
    $this->sut->createPaymentMethod($customer, $this->createPaymentMethodDTO());

    // then
    $this->expectExceptionObject(new AssertionFailedError('Failed asserting that a given payment method was created.'));

    // when
    $this->sut->assertPaymentMethodCreated(function (GatewayPaymentMethodDTO $data) {
        return $data->id === (string) Str::uuid();
    });
});

test('assertPaymentMethodCreated correctly asserts created payment methods', function () {
    // given
    $customer = $this->sut->mockExistingCustomer();
    $this->sut->createPaymentMethod($customer, $this->createPaymentMethodDTO());
    $paymentMethod = $this->sut->createPaymentMethod($customer, $this->createPaymentMethodDTO());
    $defaultPaymentMethod = $this->sut->createPaymentMethod($customer, $this->createPaymentMethodDTO(), true);

    // when
    $this->sut->assertPaymentMethodCreated();
    $this->sut->assertPaymentMethodCreated(static function (GatewayPaymentMethodDTO $data) use ($paymentMethod) {
        return $data->id === $paymentMethod->gateway_id;
    });
    $this->sut->assertPaymentMethodCreated(static function (GatewayPaymentMethodDTO $data, bool $isDefault) use ($defaultPaymentMethod) {
        return $data->id === $defaultPaymentMethod->gateway_id && $isDefault === true;
    });
});

test('assertPaymentMethodNotCreated throws when expectation at least one payment method was created', function () {
    // given
    $customer = $this->sut->mockExistingCustomer();
    $this->sut->createPaymentMethod($customer, $this->createPaymentMethodDTO());

    // then
    $this->expectExceptionObject(new AssertionFailedError('Failed asserting that no payment method was created.'));

    // when
    $this->sut->assertPaymentMethodNotCreated();
});

test('assertPaymentMethodNotCreated correctly asserts no payment method created', function () {
    // when
    $this->sut->assertPaymentMethodNotCreated();
});

test('assertInvoiceCreated throws AssertionFailedException when expectation is not provided and no invoice was created', function () {
    // then
    $this->expectExceptionObject(new AssertionFailedError('Failed asserting that any invoice was created.'));

    // when
    $this->sut->assertInvoiceCreated();
});

test('assertInvoiceCreated throws when expectation does not match any created invoice', function () {
    // given
    $customer = $this->sut->mockExistingCustomer();
    $this->sut->createInvoice($customer, $this->createInvoiceDTO());
    $this->sut->createInvoice($customer, $this->createInvoiceDTO());

    // then
    $this->expectExceptionObject(new AssertionFailedError('Failed asserting that a given invoice was created.'));

    // when
    $this->sut->assertInvoiceCreated(function (GatewayInvoiceDTO $data) {
        return $data->id === (string) Str::uuid();
    });
});

test('assertInvoiceCreated correctly asserts created invoices', function () {
    // given
    $customer = $this->sut->mockExistingCustomer();
    $this->sut->createInvoice($customer, $this->createInvoiceDTO());
    $invoice = $this->sut->createInvoice($customer, $this->createInvoiceDTO());
    $payer = $this->createCustomerDTO();
    $invoiceWithCustomPayer = $this->sut->createInvoice($customer, $this->createInvoiceDTO(), $payer);

    // when
    $this->sut->assertInvoiceCreated();
    $this->sut->assertInvoiceCreated(static function (GatewayInvoiceDTO $data) use ($invoice) {
        return $data->id === $invoice->gateway_id;
    });
    $this->sut->assertInvoiceCreated(static function (GatewayInvoiceDTO $data, CustomerDTO $invoicePayer) use ($invoiceWithCustomPayer, $payer) {
        return $data->id === $invoiceWithCustomPayer->gateway_id && $payer->document->equals($invoicePayer->document);
    });
});

test('assertInvoiceNotCreated throws when expectation at least one invoice was created', function () {
    // given
    $customer = $this->sut->mockExistingCustomer();
    $this->sut->createInvoice($customer, $this->createInvoiceDTO());

    // then
    $this->expectExceptionObject(new AssertionFailedError('Failed asserting that no invoice was created.'));

    // when
    $this->sut->assertInvoiceNotCreated();
});

test('assertInvoiceNotCreated correctly asserts no invoice created', function () {
    // when
    $this->sut->assertInvoiceNotCreated();
});

test('assertInvoicePaid throws AssertionFailedException when expectation is not provided and no invoice was paid', function () {
    // then
    $this->expectExceptionObject(new AssertionFailedError('Failed asserting that any invoice was paid.'));

    // when
    $this->sut->assertInvoicePaid();
});

test('assertInvoicePaid throws when expectation does not match any paid invoice', function () {
    // given
    $customer = $this->sut->mockExistingCustomer();
    $this->sut->createInvoice($customer, $this->createInvoiceDTO());
    $this->sut->createInvoice($customer, $this->createInvoiceDTO());

    // then
    $this->expectExceptionObject(new AssertionFailedError('Failed asserting that a given invoice was paid.'));

    // when
    $this->sut->assertInvoicePaid(function (GatewayInvoiceDTO $data) {
        return $data->id === (string) Str::uuid();
    });
});

test('assertInvoicePaid correctly asserts paid invoices', function () {
    // given
    $customer = $this->sut->mockExistingCustomer();
    $paymentMethod = $this->sut->mockExistingPaymentMethod($customer);
    $this->sut->mockExistingInvoice($customer);
    $invoicePaidUsingToken = $this->sut->mockExistingInvoice($customer);
    $invoicePaidUsingPaymentMethod = $this->sut->mockExistingInvoice($customer);
    $this->sut->chargeInvoiceUsingToken($invoicePaidUsingToken, 'some-token');
    $this->sut->chargeInvoiceUsingPaymentMethod($invoicePaidUsingPaymentMethod, $customer, $paymentMethod);

    // when
    $this->sut->assertInvoicePaid();
    $this->sut->assertInvoicePaid(static function (GatewayInvoiceDTO $data, string $externalReference, string $customerId, ?string $token) use ($invoicePaidUsingToken) {
        return $data->id === $invoicePaidUsingToken->gateway_id && 'some-token' === $token;
    });
    $this->sut->assertInvoicePaid(static function (GatewayInvoiceDTO $data, string $externalReference, string $customerId, ?string $token, ?string $paymentMethodId) use ($invoicePaidUsingPaymentMethod, $paymentMethod) {
        return $data->id === $invoicePaidUsingPaymentMethod->gateway_id && $paymentMethod->gateway_id === $paymentMethodId;
    });
});

test('assertInvoiceNotPaid throws when expectation at least one invoice was paid', function () {
    // given
    $invoice = $this->sut->mockExistingInvoice();
    $this->sut->chargeInvoiceUsingToken($invoice, 'some-token');

    // then
    $this->expectExceptionObject(new AssertionFailedError('Failed asserting that no invoice was paid.'));

    // when
    $this->sut->assertInvoiceNotPaid();
});

test('assertInvoiceNotPaid correctly asserts no invoice paid', function () {
    // when
    $this->sut->assertInvoiceNotPaid();
});

test('assertRecipientCreated throws AssertionFailedException when expectation is not provided and no customer was created', function () {
    // then
    $this->expectExceptionObject(new AssertionFailedError('Failed asserting that any recipient was created.'));

    // when
    $this->sut->assertRecipientCreated();
});

test('assertRecipientCreated throws when expectation does not match any created recipient', function () {
    // given
    $this->sut->createRecipient($this->createRecipientDTO());
    $this->sut->createRecipient($this->createRecipientDTO());

    // then
    $this->expectExceptionObject(new AssertionFailedError('Failed asserting that a given recipient was created.'));

    // when
    $this->sut->assertRecipientCreated(function (RecipientDTO $data) {
        return $data->document->equals(new Document($this->faker->cnpj(false), DocumentType::CNPJ()));
    });
});

test('assertRecipientCreated correctly asserts created recipients', function () {
    // given
    $this->sut->createRecipient($this->createRecipientDTO());
    $recipient = $this->sut->createRecipient($this->createRecipientDTO());

    // when
    $this->sut->assertRecipientCreated();
    $this->sut->assertRecipientCreated(static function (RecipientDTO $data) use ($recipient) {
        return $data->document->equals($recipient->document);
    });
});

test('assertRecipientNotCreated throws when expectation at least one recipient was created', function () {
    // given
    $this->sut->createRecipient($this->createRecipientDTO());

    // then
    $this->expectExceptionObject(new AssertionFailedError('Failed asserting that no recipient was created.'));

    // when
    $this->sut->assertRecipientNotCreated();
});

test('assertRecipientNotCreated correctly asserts no recipients created', function () {
    // when
    $this->sut->assertRecipientNotCreated();
});
