<?php

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use ValeSaude\LaravelValueObjects\JsonObject;
use ValeSaude\LaravelValueObjects\Money;
use ValeSaude\PaymentGatewayClient\Client;
use ValeSaude\PaymentGatewayClient\Customer\GatewayPaymentMethodDTO;
use ValeSaude\PaymentGatewayClient\Exceptions\UnsupportedFeatureException;
use ValeSaude\PaymentGatewayClient\Gateways\Enums\GatewayFeature;
use ValeSaude\PaymentGatewayClient\Invoice\Builders\InvoiceBuilder;
use ValeSaude\PaymentGatewayClient\Invoice\Collections\GatewayInvoiceItemDTOCollection;
use ValeSaude\PaymentGatewayClient\Invoice\Enums\InvoicePaymentMethod;
use ValeSaude\PaymentGatewayClient\Invoice\Enums\InvoiceStatus;
use ValeSaude\PaymentGatewayClient\Invoice\GatewayInvoiceDTO;
use ValeSaude\PaymentGatewayClient\Invoice\GatewayInvoiceItemDTO;
use ValeSaude\PaymentGatewayClient\Invoice\InvoiceDTO;
use ValeSaude\PaymentGatewayClient\Invoice\InvoiceItemDTO;
use ValeSaude\PaymentGatewayClient\Models\Customer;
use ValeSaude\PaymentGatewayClient\Models\Invoice;
use ValeSaude\PaymentGatewayClient\Models\PaymentMethod;
use ValeSaude\PaymentGatewayClient\Models\Recipient;
use ValeSaude\PaymentGatewayClient\Recipient\Enums\RecipientStatus;
use ValeSaude\PaymentGatewayClient\Recipient\GatewayRecipientDTO;
use ValeSaude\PaymentGatewayClient\Tests\Concerns\HasCustomerHelperMethodsTrait;
use ValeSaude\PaymentGatewayClient\Tests\Concerns\HasInvoiceHelperMethodsTrait;
use ValeSaude\PaymentGatewayClient\Tests\Concerns\HasRecipientHelperMethodsTrait;
use ValeSaude\PaymentGatewayClient\Tests\Concerns\MocksGatewayMethodsTrait;
use function PHPUnit\Framework\callback;
use function PHPUnit\Framework\never;
use function PHPUnit\Framework\once;

uses(
    HasCustomerHelperMethodsTrait::class,
    HasInvoiceHelperMethodsTrait::class,
    HasRecipientHelperMethodsTrait::class,
    MocksGatewayMethodsTrait::class
);

beforeEach(function () {
    $this->createGatewayMock();
    $this->sut = new Client($this->gatewayMock);
});

test('createCustomer creates a customer using its gateway and returns a Customer instance when gateway supports CUSTOMER feature', function () {
    // given
    $data = $this->createCustomerDTO();
    $expectedId = $this->faker->uuid;
    $this->mockGatewaySupportedFeature(GatewayFeature::CUSTOMER());
    $this->gatewayMock
        ->expects(once())
        ->method('createCustomer')
        ->with($data)
        ->willReturnCallback(static fn () => $expectedId);

    // when
    $customer = $this->sut->createCustomer($data);

    // then
    expect($customer->gateway_id)->toEqual($expectedId)
        ->and($customer->gateway_slug)->toEqual('mock');
    $this->expectCustomerToBeEqualsToData($customer, $data);
});

test('createCustomer creates a Customer internally and returns when gateway does not support CUSTOMER feature', function () {
    // given
    $data = $this->createCustomerDTO();
    $this->mockGatewaySupportedFeature(GatewayFeature::CUSTOMER(), false);
    $this->gatewayMock
        ->expects(never())
        ->method('createCustomer');

    // when
    $customer = $this->sut->createCustomer($data);

    // then
    expect($customer->gateway_id)->toBeNull();
    $this->expectCustomerToBeEqualsToData($customer, $data);
});

test('updateCustomer updates an existing customer using its gateway and returns the updated Customer instance when gateway supports CUSTOMER feature', function () {
    // given
    $customer = Customer::factory()->create();
    $data = $this->createCustomerDTO();
    $this->mockGatewaySupportedFeature(GatewayFeature::CUSTOMER());
    $this->gatewayMock
        ->expects(once())
        ->method('updateCustomer')
        ->with($customer->gateway_id, $data);

    // when
    $customer = $this->sut->updateCustomer($customer, $data);

    // then
    $this->expectCustomerToBeEqualsToData($customer, $data);
});

test('updateCustomer updates an existing Customer internally and returns the updated instance when gateway does not support CUSTOMER feature', function () {
    // given
    $customer = Customer::factory()->create();
    $data = $this->createCustomerDTO();
    $this->mockGatewaySupportedFeature(GatewayFeature::CUSTOMER(), false);
    $this->gatewayMock
        ->expects(never())
        ->method('updateCustomer');

    // when
    $customer = $this->sut->updateCustomer($customer, $data);

    // then
    $this->expectCustomerToBeEqualsToData($customer, $data);
});

test('createPaymentMethod throws when gateway does not support PAYMENT_METHOD feature', function () {
    // given
    $customer = Customer::factory()->create();
    $data = $this->createPaymentMethodDTO();
    $this->mockGatewaySupportedFeature(GatewayFeature::PAYMENT_METHOD(), false);

    // when
    $this->sut->createPaymentMethod($customer, $data);
})->throws(
    UnsupportedFeatureException::class,
    'The gateway "mock" does not support "PAYMENT_METHOD" feature.'
);

test('createPaymentMethod creates a payment method using its gateway and returns a PaymentMethod instance when gateway supports PAYMENT_METHOD feature', function () {
    // given
    $customer = Customer::factory()->create();
    $data = $this->createPaymentMethodDTO();
    $expectedId = $this->faker->uuid();
    $creditCard = $this->createCreditCard();
    $this->mockGatewaySupportedFeature(GatewayFeature::PAYMENT_METHOD());
    $this->gatewayMock
        ->expects(once())
        ->method('createPaymentMethod')
        ->with($customer->gateway_id, $data, false)
        ->willReturnCallback(static fn () => new GatewayPaymentMethodDTO($expectedId, $creditCard));

    // when
    $paymentMethod = $this->sut->createPaymentMethod($customer, $data, false);

    // then
    expect($paymentMethod->gateway_id)->toEqual($expectedId)
        ->and($paymentMethod->gateway_slug)->toEqual('mock')
        ->and($paymentMethod->is_default)->toBeFalse()
        ->and($paymentMethod->description)->toEqual($data->description)
        ->and($paymentMethod->card)->toEqual($creditCard)
        ->and($customer->paymentMethods()->count())->toEqual(1);
});

test('createPaymentMethod updates default payment method when setAsDefault is true', function () {
    // given
    $customer = Customer::factory()->create();
    $previouslyDefaultPaymentMethod = PaymentMethod
        ::factory()
        ->for($customer)
        ->asDefault()
        ->create();
    $data = $this->createPaymentMethodDTO();
    $expectedId = $this->faker->uuid();
    $creditCard = $this->createCreditCard();
    $this->mockGatewaySupportedFeature(GatewayFeature::PAYMENT_METHOD());
    $this->gatewayMock
        ->expects(once())
        ->method('createPaymentMethod')
        ->with($customer->gateway_id, $data, true)
        ->willReturnCallback(static fn () => new GatewayPaymentMethodDTO($expectedId, $creditCard));

    // when
    $paymentMethod = $this->sut->createPaymentMethod($customer, $data, true);

    // then
    expect($paymentMethod->is_default)->toBeTrue()
        ->and($paymentMethod->description)->toEqual($data->description)
        ->and($previouslyDefaultPaymentMethod->refresh()->is_default)->toBeFalse();
});

test('deletePaymentMethod throws when gateway does not support PAYMENT_METHOD feature', function () {
    // given
    $method = PaymentMethod::factory()->create();
    $this->mockGatewaySupportedFeature(GatewayFeature::PAYMENT_METHOD(), false);

    // when
    $this->sut->deletePaymentMethod($method);
})->throws(
    UnsupportedFeatureException::class,
    'The gateway "mock" does not support "PAYMENT_METHOD" feature.'
);

test('deletePaymentMethod deletes a payment method using its gateway when gateway supports PAYMENT_METHOD feature', function () {
    // given
    $method = PaymentMethod::factory()->create();
    $this->mockGatewaySupportedFeature(GatewayFeature::PAYMENT_METHOD());
    $this->gatewayMock
        ->expects(once())
        ->method('deletePaymentMethod')
        ->with($method->customer->gateway_id, $method->gateway_id);

    // when
    $this->sut->deletePaymentMethod($method);

    // then
    expect($method->deleted_at)->not->toBeNull();
});

test('createInvoice creates an invoice using its gateway and returns and Invoice instance when gateway supports INVOICE feature', function () {
    // given
    $recipient = Recipient::factory()->create();
    $customer = Customer::factory()->create();
    $item1 = new GatewayInvoiceItemDTO('some-item-id-1', new Money(1000), 1, 'Item 1 description');
    $item2 = new GatewayInvoiceItemDTO('some-item-id-2', new Money(2000), 3, 'Item 2 description');
    $gatewayItems = GatewayInvoiceItemDTOCollection::make()
        ->add($item1)
        ->add($item2);
    $data = InvoiceBuilder::make()
        ->setDueDate(CarbonImmutable::now()->addWeek())
        ->setMaxInstallments(12)
        ->setAvailablePaymentMethods(InvoicePaymentMethod::CREDIT_CARD())
        ->addItem(InvoiceItemDTO::fromGatewayInvoiceItemDTO($item1))
        ->addItem(InvoiceItemDTO::fromGatewayInvoiceItemDTO($item2))
        ->addSplit($recipient, new Money(500))
        ->get();
    $expectedURL = 'https://some.url/some-invoice-id';
    $expectedInvoiceId = 'some-invoice-id';
    $expectedBankSlipCode = 'some-bank-slip-code';
    $expectedPixCode = 'some-pix-code';
    $this->mockGatewayMultipleSupportedFeatures([
        GatewayFeature::INVOICE()->value => true,
        GatewayFeature::INVOICE_SPLIT()->value => true,
        GatewayFeature::RECIPIENT()->value => true,
    ]);
    $this->gatewayMock
        ->expects(once())
        ->method('createInvoice')
        ->with($customer->gateway_id, $data)
        ->willReturnCallback(function () use ($data, $gatewayItems, $expectedURL, $expectedInvoiceId, $expectedBankSlipCode, $expectedPixCode) {
            return new GatewayInvoiceDTO(
                $expectedInvoiceId,
                $expectedURL,
                $data->dueDate,
                InvoiceStatus::PENDING(),
                $gatewayItems,
                null,
                $expectedBankSlipCode,
                $expectedPixCode
            );
        });

    // when
    $invoice = $this->sut->createInvoice($customer, $data);

    // then
    expect($invoice->gateway_id)->toEqual($expectedInvoiceId)
        ->and($invoice->gateway_slug)->toEqual('mock')
        ->and($invoice->url)->toEqual($expectedURL)
        ->and($invoice->bank_slip_code)->toEqual($expectedBankSlipCode)
        ->and($invoice->pix_code)->toEqual($expectedPixCode)
        ->and($invoice->status)->toEqual(InvoiceStatus::PENDING())
        ->and($invoice->installments)->toBeNull();
    $this->expectInvoiceToBeEqualsToData($invoice, $data);
    $this->expectInvoiceToContainAllGatewayItems($invoice, $gatewayItems);
});

test('createInvoice creates an invoice using its gateway without sending splits when RECIPIENT feature is not supported', function () {
    // given
    $recipient = Recipient::factory()->create();
    $customer = Customer::factory()->create();
    $item = new InvoiceItemDTO(new Money(1000), 1, 'Some description');
    $data = InvoiceBuilder::make()
        ->addItem($item)
        ->addSplit($recipient, new Money(500))
        ->get();
    $this->mockGatewayMultipleSupportedFeatures([
        GatewayFeature::INVOICE()->value => true,
        GatewayFeature::INVOICE_SPLIT()->value => true,
    ]);
    $this->gatewayMock
        ->expects(once())
        ->method('createInvoice')
        ->with(
            $customer->gateway_id,
            callback(fn (InvoiceDTO $data) => null === $data->splits)
        )
        ->willReturnCallback(function () use ($data, $item) {
            return new GatewayInvoiceDTO(
                'some-invoice-id',
                'https://some.url/some-invoice-id',
                $data->dueDate,
                InvoiceStatus::PENDING(),
                GatewayInvoiceItemDTOCollection::make()
                    ->add(GatewayInvoiceItemDTO::fromInvoiceItemDTO($item))
            );
        });

    // when
    $invoice = $this->sut->createInvoice($customer, $data);

    // then
    $this->expectInvoiceToBeEqualsToData($invoice, $data);
});

test('createInvoice creates allows specifying custom payer for invoice', function () {
    // given
    $customer = Customer::factory()->create();
    $item = new InvoiceItemDTO(new Money(1000), 1, 'Some description');
    $data = InvoiceBuilder::make()
        ->addItem($item)
        ->get();
    $payer = $this->createCustomerDTO();
    $this->mockGatewaySupportedFeature(GatewayFeature::INVOICE());
    $this->gatewayMock
        ->expects(once())
        ->method('createInvoice')
        ->with($customer->gateway_id, $data, $payer)
        ->willReturnCallback(function () use ($data, $item) {
            return new GatewayInvoiceDTO(
                'some-invoice-id',
                'https://some.url/some-invoice-id',
                $data->dueDate,
                InvoiceStatus::PENDING(),
                GatewayInvoiceItemDTOCollection::make()
                    ->add(GatewayInvoiceItemDTO::fromInvoiceItemDTO($item))
            );
        });

    // when
    $this->sut->createInvoice($customer, $data, $payer);
});

test('createInvoice creates an Invoice internally and returns when gateway does not support INVOICE feature', function () {
    // given
    $customer = Customer::factory()->create();
    $data = InvoiceBuilder::make()
        ->setDueDate(CarbonImmutable::now()->addWeek())
        ->setMaxInstallments(12)
        ->addItem(new InvoiceItemDTO(new Money(1000), 1, 'Item 1 description'))
        ->addItem(new InvoiceItemDTO(new Money(2000), 3, 'Item 2 description'))
        ->get();
    $this->mockGatewaySupportedFeature(GatewayFeature::INVOICE(), false);
    $this->gatewayMock
        ->expects(never())
        ->method('createInvoice');

    // when
    $invoice = $this->sut->createInvoice($customer, $data);

    // then
    expect($invoice->gateway_id)->toBeNull()
        ->and($customer->gateway_slug)->toEqual('mock')
        ->and($invoice->bank_slip_code)->toBeNull()
        ->and($invoice->pix_code)->toBeNull()
        ->and($invoice->status)->toEqual(InvoiceStatus::PENDING());
    $this->expectInvoiceToBeEqualsToData($invoice, $data);
    $this->expectInvoiceToContainAllItems($invoice, $data->items);
});

test('refreshInvoiceStatus updates invoice status and related fields and returns updated instance', function () {
    // given
    $invoice = Invoice::factory()->create();
    $paidAt = CarbonImmutable::today();
    $this->mockGatewaySupportedFeature(GatewayFeature::INVOICE());
    $this->gatewayMock
        ->expects(once())
        ->method('getInvoice')
        ->with($invoice->gateway_id)
        ->willReturn(
            new GatewayInvoiceDTO(
                $invoice->gateway_id,
                $invoice->url,
                $invoice->due_date,
                InvoiceStatus::PAID(),
                new GatewayInvoiceItemDTOCollection(),
                1,
                $invoice->bank_slip_code,
                $invoice->pix_code,
                $paidAt,
            )
        );

    // when
    $this->sut->refreshInvoiceStatus($invoice);

    // then
    expect($invoice->status->equals(InvoiceStatus::PAID()))->toBeTrue()
        ->and($invoice->paid_at->toDateString())->toEqual($paidAt->toDateString())
        ->and($invoice->installments)->toEqual(1);
});

test('refreshInvoiceStatus throws when gateway does not support INVOICE feature', function () {
    // given
    $invoice = Invoice::factory()->create();
    $this->mockGatewaySupportedFeature(GatewayFeature::INVOICE(), false);

    // when
    $this->sut->refreshInvoiceStatus($invoice);
})->throws(
    UnsupportedFeatureException::class,
    'The gateway "mock" does not support "INVOICE" feature.'
);

test('createPaymentMethod throws when gateway does not support INVOICE_SPLIT feature', function () {
    // given
    $recipient = Recipient::factory()->create();
    $customer = Customer::factory()->create();
    $data = InvoiceBuilder::make()
        ->addSplit($recipient, new Money(1000))
        ->get();
    $this->mockGatewaySupportedFeature(GatewayFeature::INVOICE_SPLIT(), false);

    // when
    $this->sut->createInvoice($customer, $data);
})->throws(
    UnsupportedFeatureException::class,
    'The gateway "mock" does not support "INVOICE_SPLIT" feature.'
);

test('chargeInvoiceUsingPaymentMethod charges an invoice using its gateway and returns the paid Invoice instance', function () {
    // given
    $invoice = Invoice::factory()->create();
    $customer = $invoice->customer;
    $paymentMethod = PaymentMethod
        ::factory()
        ->for($customer)
        ->create();
    $installments = 2;
    $this->gatewayMock
        ->expects(once())
        ->method('chargeInvoiceUsingPaymentMethod')
        ->with($invoice->gateway_id, $customer->gateway_id, $paymentMethod->gateway_id, $installments);

    // when
    $this->sut->chargeInvoiceUsingPaymentMethod($invoice, $customer, $paymentMethod, $installments);

    // then
    expect($invoice->status->equals(InvoiceStatus::PAID()))->toBeTrue()
        ->and($invoice->paid_at->toDateString())->toEqual(CarbonImmutable::today()->toDateString())
        ->and($invoice->installments)->toEqual($installments);
});

test('chargeInvoiceUsingPaymentMethod charges using the default Customer payment method when none is provided', function () {
    // given
    $invoice = Invoice::factory()->create();
    $customer = $invoice->customer;
    $paymentMethod = PaymentMethod
        ::factory()
        ->for($customer)
        ->asDefault()
        ->create();
    $this->gatewayMock
        ->expects(once())
        ->method('chargeInvoiceUsingPaymentMethod')
        ->with($invoice->gateway_id, $customer->gateway_id, $paymentMethod->gateway_id);

    // when
    $this->sut->chargeInvoiceUsingPaymentMethod($invoice, $customer);

    // then
    expect($invoice->status->equals(InvoiceStatus::PAID()))->toBeTrue()
        ->and($invoice->paid_at->toDateString())->toEqual(CarbonImmutable::today()->toDateString());
});

test('chargeInvoiceUsingPaymentMethod throws when no payment method is provided and Customer does not have a default one', function () {
    // given
    $invoice = Invoice::factory()->create();
    $customer = $invoice->customer;

    // when
    $this->sut->chargeInvoiceUsingPaymentMethod($invoice, $customer);
})->throws(
    InvalidArgumentException::class,
    'The customer does not have a default payment method.'
);

test('chargeInvoiceUsingToken charges an invoice using its gateway and returns the paid Invoice instance', function () {
    // given
    $invoice = Invoice::factory()->create();
    $token = 'some-token';
    $installments = 3;
    $this->gatewayMock
        ->expects(once())
        ->method('chargeInvoiceUsingToken')
        ->with($invoice->gateway_id, $token, $installments);

    // when
    $this->sut->chargeInvoiceUsingToken($invoice, $token, $installments);

    // then
    expect($invoice->status->equals(InvoiceStatus::PAID()))->toBeTrue()
        ->and($invoice->paid_at->toDateString())->toEqual(CarbonImmutable::today()->toDateString())
        ->and($invoice->installments)->toEqual($installments);
});

test('refundInvoice partially throws when gateway does not support INVOICE_PARTIAL_REFUND feature and refundAmount is provided', function () {
    // given
    $invoice = Invoice::factory()->paid()->create();
    $this->mockGatewaySupportedFeature(GatewayFeature::INVOICE_PARTIAL_REFUND(), false);

    // when
    $this->sut->refundInvoice($invoice, new Money(999999));
})->throws(
    UnsupportedFeatureException::class,
    'The gateway "mock" does not support "INVOICE_PARTIAL_REFUND" feature.'
);

test('refundInvoice refunds an invoice using its gateway and returns the refunded Invoice instance', function () {
    // given
    $invoice = Invoice::factory()->paid()->create();
    $this->gatewayMock
        ->expects(once())
        ->method('refundInvoice')
        ->with($invoice->gateway_id);

    // when
    $this->sut->refundInvoice($invoice);

    // then
    expect($invoice->status->equals(InvoiceStatus::REFUNDED()))->toBeTrue()
        ->and($invoice->refunded_at->toDateString())->toEqual(CarbonImmutable::today()->toDateString())
        ->and($invoice->refunded_amount)->toEqual($invoice->total);
});

test('refundInvoice partially refunds an invoice using its gateway and returns the refunded Invoice instance when refundAmount is provided', function () {
    // given
    $this->mockGatewaySupportedFeature(GatewayFeature::INVOICE_PARTIAL_REFUND());
    $invoice = Invoice::factory()->paid()->create();
    $refundAmount = new Money(999999);
    $this->gatewayMock
        ->expects(once())
        ->method('refundInvoice')
        ->with($invoice->gateway_id, $refundAmount);

    // when
    $this->sut->refundInvoice($invoice, $refundAmount);

    // then
    expect($invoice->status->equals(InvoiceStatus::REFUNDED()))->toBeTrue()
        ->and($invoice->refunded_at->toDateString())->toEqual(CarbonImmutable::today()->toDateString())
        ->and($invoice->refunded_amount)->toEqual($refundAmount);
});

test('createRecipient creates a recipient using its gateway and returns the Recipient instance when gateway supports RECIPIENT feature', function () {
    // given
    $data = $this->createRecipientDTO();
    $data->gatewaySpecificData = $data->gatewaySpecificData->set('some-input-gateway-data', 1);
    $this->mockGatewaySupportedFeature(GatewayFeature::RECIPIENT());
    $this->gatewayMock
        ->expects(once())
        ->method('createRecipient')
        ->with($data)
        ->willReturn(
            new GatewayRecipientDTO(
                Str::uuid(),
                RecipientStatus::APPROVED(),
                new JsonObject(['some-output-gateway-data' => 2])
            )
        );

    // when
    $recipient = $this->sut->createRecipient($data);

    // then
    expect($recipient->name)->toEqual($data->name)
        ->and($recipient->document->equals($data->document))->toBeTrue()
        ->and($recipient->address->equals($data->address))->toBeTrue()
        ->and($recipient->phone->equals($data->phone))->toBeTrue()
        ->and($recipient->bank_account->equals($data->bankAccount))->toBeTrue()
        ->and($recipient->automatic_withdrawal)->toEqual($data->automaticWithdrawal)
        ->and($recipient->status->equals(RecipientStatus::APPROVED()))
        ->and($recipient->gateway_id)->not->toStartWith('fake-')
        ->and($recipient->gateway_specific_data->toArray())
        ->toHaveKey('some-input-gateway-data', 1)
        ->toHaveKey('some-output-gateway-data', 2);
});

test('createRecipient creates a Recipient internally and returns when gateway does not support RECIPIENT feature', function () {
    // given
    $data = $this->createRecipientDTO();
    $this->mockGatewaySupportedFeature(GatewayFeature::RECIPIENT(), false);
    $this->gatewayMock
        ->expects(never())
        ->method('createRecipient');

    // when
    $recipient = $this->sut->createRecipient($data);

    // then
    expect($recipient->name)->toEqual($data->name)
        ->and($recipient->document->equals($data->document))->toBeTrue()
        ->and($recipient->address->equals($data->address))->toBeTrue()
        ->and($recipient->phone->equals($data->phone))->toBeTrue()
        ->and($recipient->bank_account->equals($data->bankAccount))->toBeTrue()
        ->and($recipient->automatic_withdrawal)->toEqual($data->automaticWithdrawal)
        ->and($recipient->status->equals(RecipientStatus::APPROVED()))
        ->and($recipient->gateway_id)->toStartWith('fake-')
        ->and($recipient->gateway_specific_data->toArray())->toBeEmpty();
});
