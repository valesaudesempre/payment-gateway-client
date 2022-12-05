<?php

use Carbon\CarbonImmutable;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use ValeSaude\PaymentGatewayClient\Gateways\Exceptions\TransactionDeclinedException;
use ValeSaude\PaymentGatewayClient\Gateways\Iugu\Exceptions\GenericErrorResponseException;
use ValeSaude\PaymentGatewayClient\Gateways\Iugu\IuguGateway;
use ValeSaude\PaymentGatewayClient\Invoice\Builders\InvoiceBuilder;
use ValeSaude\PaymentGatewayClient\Invoice\Enums\InvoicePaymentMethod;
use ValeSaude\PaymentGatewayClient\Invoice\Enums\InvoiceStatus;
use ValeSaude\PaymentGatewayClient\Invoice\InvoiceItemDTO;
use ValeSaude\PaymentGatewayClient\Models\Recipient;
use ValeSaude\PaymentGatewayClient\Tests\Concerns\HasCustomerHelperMethodsTrait;
use ValeSaude\PaymentGatewayClient\ValueObjects\Money;

uses(HasCustomerHelperMethodsTrait::class);

$baseUrl = 'https://some.url';

beforeEach(fn () => $this->sut = new IuguGateway($baseUrl, 'some-api-key'));

test('createCustomer POST to /v1/customers and returns external id on success', function () use ($baseUrl) {
    // given
    $expectedExternalId = 'some-external-id';
    $expectedInternalId = 'some-internal-id';
    $data = $this->createCustomerDTO();
    Http::fake(["{$baseUrl}/v1/customers" => Http::response(['id' => $expectedExternalId])]);

    // when
    $id = $this->sut->createCustomer($data, $expectedInternalId);

    // then
    expect($id)->toEqual($expectedExternalId);
    Http::assertSent(static function (Request $request) use ($expectedInternalId, $data) {
        $body = $request->data();

        return data_get($body, 'email') === (string) $data->email &&
            data_get($body, 'name') === $data->name &&
            data_get($body, 'cpf_cnpj') === $data->document->getNumber() &&
            data_get($body, 'zip_code') === (string) $data->address->getZipCode() &&
            data_get($body, 'number') === $data->address->getNumber() &&
            data_get($body, 'street') === $data->address->getStreet() &&
            data_get($body, 'city') === $data->address->getCity() &&
            data_get($body, 'state') === $data->address->getState() &&
            data_get($body, 'district') === $data->address->getDistrict() &&
            data_get($body, 'complement') === $data->address->getComplement() &&
            data_get($body, 'custom_variables.0.name') === 'external_reference' &&
            data_get($body, 'custom_variables.0.value') === $expectedInternalId;
    });
});

test('createCustomer throws RequestException on HTTP error response', function () use ($baseUrl) {
    // given
    $data = $this->createCustomerDTO();
    Http::fake(["{$baseUrl}/v1/customers" => Http::response(null, 400)]);

    // when
    $this->sut->createCustomer($data, 'some-internal-id');
})->throws(RequestException::class);

test('updateCustomer PUT to /v1/customers/{id}', function () use ($baseUrl) {
    // given
    $expectedExternalId = 'some-external-id';
    $data = $this->createCustomerDTO();
    Http::fake(["{$baseUrl}/v1/customers/{$expectedExternalId}" => Http::response()]);

    // when
    $this->sut->updateCustomer($expectedExternalId, $data);

    // then
    Http::assertSent(static function (Request $request) use ($data) {
        $body = $request->data();

        return data_get($body, 'email') === (string) $data->email &&
            data_get($body, 'name') === $data->name &&
            data_get($body, 'cpf_cnpj') === $data->document->getNumber() &&
            data_get($body, 'zip_code') === (string) $data->address->getZipCode() &&
            data_get($body, 'number') === $data->address->getNumber() &&
            data_get($body, 'street') === $data->address->getStreet() &&
            data_get($body, 'city') === $data->address->getCity() &&
            data_get($body, 'state') === $data->address->getState() &&
            data_get($body, 'district') === $data->address->getDistrict() &&
            data_get($body, 'complement') === $data->address->getComplement();
    });
});

test('updateCustomer throws RequestException on HTTP error response', function () use ($baseUrl) {
    // given
    $expectedExternalId = 'some-external-id';
    $data = $this->createCustomerDTO();
    Http::fake(["{$baseUrl}/v1/customers/{$expectedExternalId}" => Http::response(null, 400)]);

    // when
    $this->sut->updateCustomer($expectedExternalId, $data);
})->throws(RequestException::class);

test('createPaymentMethod POST to v1/customers/{customer_id}/payment_methods and returns GatewayPaymentMethodDTO on success', function () use ($baseUrl) {
    // given
    $customerId = 'some-customer-id';
    $expectedExternalId = 'some-external-id';
    $creditCard = $this->createCreditCard();
    $data = $this->createPaymentMethodDTO();
    Http::fake([
        "{$baseUrl}/v1/customers/{$customerId}/payment_methods" => Http::response([
            'id' => $expectedExternalId,
            'data' => [
                'holder_name' => $creditCard->getHolderName(),
                'display_number' => $creditCard->getNumber(),
                'brand' => $creditCard->getBrand(),
                'month' => $creditCard->getExpirationMonth()->getValue(),
                'year' => $creditCard->getExpirationYear()->getValue(),
            ],
        ]),
    ]);

    // when
    $paymentMethod = $this->sut->createPaymentMethod($customerId, $data);

    // then
    expect($paymentMethod->id)->toEqual($expectedExternalId)
        ->and($paymentMethod->card)->toEqual($creditCard);
    Http::assertSent(static function (Request $request) use ($data) {
        $body = $request->data();

        return data_get($body, 'description') === $data->description &&
            data_get($body, 'token') === $data->token;
    });
});

test('createPaymentMethod throws RequestException on HTTP error response', function () use ($baseUrl) {
    // given
    $customerId = 'some-customer-id';
    $data = $this->createPaymentMethodDTO();
    Http::fake(["{$baseUrl}/v1/customers/{$customerId}/payment_methods" => Http::response(null, 400)]);

    // when
    $this->sut->createPaymentMethod($customerId, $data);
})->throws(RequestException::class);

test('createInvoice POST to v1/invoices and returns GatewayInvoiceDTO on success', function () use ($baseUrl) {
    // given
    $recipient = new Recipient(['gateway_id' => 'some-recipient-id']);
    $customerId = 'some-customer-id';
    $dueDate = CarbonImmutable::now()->addWeek();
    $item1 = new InvoiceItemDTO(new Money(1000), 1, 'Item 1 description');
    $item2 = new InvoiceItemDTO(new Money(2000), 3, 'Item 2 description');
    $data = InvoiceBuilder::make()
        ->setDueDate($dueDate)
        ->setMaxInstallments(12)
        ->setAvailablePaymentMethods(
            InvoicePaymentMethod::PIX(),
            InvoicePaymentMethod::BANK_SLIP()
        )
        ->addItem($item1)
        ->addItem($item2)
        ->addSplit($recipient, new Money(500))
        ->get();
    $expectedExternalURL = 'https://some.url/some-invoice-id';
    $expectedExternalId = 'some-external-id';
    $payer = $this->createCustomerDTO();
    Http::fake([
        "{$baseUrl}/v1/invoices" => Http::response([
            'id' => 'some-invoice-id',
            'due_date' => $dueDate->toDateString(),
            'status' => 'pending',
            'secure_url' => $expectedExternalURL,
            'pix' => [
                'qrcode_text' => 'some-pix-code',
            ],
            'bank_slip' => [
                'digitable_line' => 'some-bank-slip-code',
            ],
            'items' => [
                [
                    'id' => 'some-item-id-1',
                    'description' => $item1->description,
                    'price_cents' => $item1->price->getCents(),
                    'quantity' => $item1->quantity,
                ],
                [
                    'id' => 'some-item-id-2',
                    'description' => $item2->description,
                    'price_cents' => $item2->price->getCents(),
                    'quantity' => $item2->quantity,
                ],
            ],
        ]),
    ]);

    // when
    $invoice = $this->sut->createInvoice($customerId, $data, $payer, $expectedExternalId);

    // then
    expect($invoice->id)->toEqual('some-invoice-id')
        ->and($invoice->url)->toEqual($expectedExternalURL)
        ->and($invoice->dueDate->toDateString())->toEqual($dueDate->toDateString())
        ->and($invoice->status->equals(InvoiceStatus::PENDING()))->toBeTrue()
        ->and($invoice->items->getItems()[0]->id)->toEqual('some-item-id-1')
        ->and($invoice->items->getItems()[1]->id)->toEqual('some-item-id-2')
        ->and($invoice->pixCode)->toEqual('some-pix-code')
        ->and($invoice->bankSlipCode)->toEqual('some-bank-slip-code');
    Http::assertSent(static function (Request $request) use ($payer, $customerId, $expectedExternalId, $data) {
        $body = $request->data();
        $expectedPayerPayload = [
            'cpf_cnpj' => $payer->document->getNumber(),
            'name' => $payer->name,
            'email' => (string) $payer->email,
            'address' => [
                'zip_code' => (string) $payer->address->getZipCode(),
                'street' => $payer->address->getStreet(),
                'number' => $payer->address->getNumber(),
                'district' => $payer->address->getDistrict(),
                'city' => $payer->address->getCity(),
                'state' => $payer->address->getState(),
                'complement' => $payer->address->getComplement(),
                'country' => 'Brasil',
            ],
        ];

        if (data_get($request, 'payer') !== $expectedPayerPayload) {
            return false;
        }

        $expectedItemsPayload = [
            ['description' => 'Item 1 description', 'quantity' => 1, 'price_cents' => 1000],
            ['description' => 'Item 2 description', 'quantity' => 3, 'price_cents' => 2000],
        ];

        if (data_get($request, 'items') !== $expectedItemsPayload) {
            return false;
        }

        $expectedSplitsPayload = [
            ['recipient_account_id' => 'some-recipient-id', 'cents' => 500],
        ];

        if (data_get($request, 'splits') !== $expectedSplitsPayload) {
            return false;
        }

        return data_get($body, 'customer_id') === $customerId &&
            data_get($body, 'due_date') === $data->dueDate->toDateString() &&
            data_get($body, 'max_installments_value') === $data->maxInstallments &&
            data_get($body, 'payable_with') === ['pix', 'bank_slip'] &&
            data_get($body, 'external_reference') === $expectedExternalId;
    });
});

test('getInvoice GET to v1/invoices/{invoice_id} and returns GatewayInvoiceDTO on success', function () use ($baseUrl) {
    // given
    $invoiceId = 'some-invoice-id';
    $dueDate = CarbonImmutable::now()->addWeek();
    $paidAt = CarbonImmutable::now()->addDay();
    $item1 = new InvoiceItemDTO(new Money(1000), 1, 'Item 1 description');
    $item2 = new InvoiceItemDTO(new Money(2000), 3, 'Item 2 description');
    $expectedExternalURL = 'https://some.url/some-invoice-id';
    Http::fake([
        "{$baseUrl}/v1/invoices/{$invoiceId}" => Http::response([
            'id' => 'some-invoice-id',
            'due_date' => $dueDate->toDateString(),
            'status' => 'paid',
            'secure_url' => $expectedExternalURL,
            'paid_at' => $paidAt->toDateString(),
            'pix' => [
                'qrcode_text' => 'some-pix-code',
            ],
            'bank_slip' => [
                'digitable_line' => 'some-bank-slip-code',
            ],
            'items' => [
                [
                    'id' => 'some-item-id-1',
                    'description' => $item1->description,
                    'price_cents' => $item1->price->getCents(),
                    'quantity' => $item1->quantity,
                ],
                [
                    'id' => 'some-item-id-2',
                    'description' => $item2->description,
                    'price_cents' => $item2->price->getCents(),
                    'quantity' => $item2->quantity,
                ],
            ],
        ]),
    ]);

    // when
    $invoice = $this->sut->getInvoice($invoiceId);

    // then
    expect($invoice->id)->toEqual('some-invoice-id')
        ->and($invoice->url)->toEqual($expectedExternalURL)
        ->and($invoice->dueDate->toDateString())->toEqual($dueDate->toDateString())
        ->and($invoice->status->equals(InvoiceStatus::PAID()))->toBeTrue()
        ->and($invoice->items->getItems()[0]->id)->toEqual('some-item-id-1')
        ->and($invoice->items->getItems()[1]->id)->toEqual('some-item-id-2')
        ->and($invoice->pixCode)->toEqual('some-pix-code')
        ->and($invoice->bankSlipCode)->toEqual('some-bank-slip-code')
        ->and($invoice->paidAt->toDateString())->toEqual($paidAt->toDateString());
});

test('chargeInvoiceUsingPaymentMethod POST to /v1/charge', function () use ($baseUrl) {
    // given
    $invoiceId = 'some-invoice-id';
    $customerId = 'some-customer-id';
    $paymentMethodId = 'some-payment-method-id';
    Http::fake(["{$baseUrl}/v1/charge" => Http::response(['success' => true])]);

    // when
    $this->sut->chargeInvoiceUsingPaymentMethod($invoiceId, $customerId, $paymentMethodId);

    // then
    Http::assertSent(static function (Request $request) use ($invoiceId, $customerId, $paymentMethodId) {
        $body = $request->data();

        return data_get($body, 'invoice_id') === $invoiceId &&
            data_get($body, 'customer_id') === $customerId &&
            data_get($body, 'customer_payment_method_id') === $paymentMethodId;
    });
});

test('chargeInvoiceUsingPaymentMethod throws TransactionDeclinedException when authorization fails', function () use ($baseUrl) {
    // given
    Http::fake(["{$baseUrl}/v1/charge" => Http::response(['success' => false, 'LR' => '01'])]);

    // when
    $this->sut->chargeInvoiceUsingPaymentMethod('some-invoice-id', 'some-customer-id', 'some-payment-method-id');
})->throws(
    TransactionDeclinedException::class,
    'Transaction declined with LR 01.'
);

test('chargeInvoiceUsingPaymentMethod throws GenericErrorResponseException on HTTP success containing errors property', function () use ($baseUrl) {
    // given
    Http::fake(["{$baseUrl}/v1/charge" => Http::response(['errors' => 'Some error'])]);

    // when
    $this->sut->chargeInvoiceUsingPaymentMethod('some-invoice-id', 'some-customer-id', 'some-payment-method-id');
})->throws(
    GenericErrorResponseException::class,
    'There was an error during the request.'
);

test('chargeInvoiceUsingPaymentMethod throws RequestException on HTTP error response', function () use ($baseUrl) {
    // given
    Http::fake(["{$baseUrl}/v1/charge" => Http::response(null, 400)]);

    // when
    $this->sut->chargeInvoiceUsingPaymentMethod('some-invoice-id', 'some-customer-id', 'some-payment-method-id');
})->throws(RequestException::class);

test('chargeInvoiceUsingToken POST to /v1/charge', function () use ($baseUrl) {
    // given
    $invoiceId = 'some-invoice-id';
    $token = 'some-token';
    Http::fake(["{$baseUrl}/v1/charge" => Http::response(['success' => true])]);

    // when
    $this->sut->chargeInvoiceUsingToken($invoiceId, $token);

    // then
    Http::assertSent(static function (Request $request) use ($invoiceId, $token) {
        $body = $request->data();

        return data_get($body, 'invoice_id') === $invoiceId &&
            data_get($body, 'token') === $token;
    });
});

test('chargeInvoiceUsingToken throws TransactionDeclinedException when authorization fails', function () use ($baseUrl) {
    // given
    Http::fake(["{$baseUrl}/v1/charge" => Http::response(['success' => false, 'LR' => '01'])]);

    // when
    $this->sut->chargeInvoiceUsingToken('some-invoice-id', 'some-token');
})->throws(
    TransactionDeclinedException::class,
    'Transaction declined with LR 01.'
);

test('chargeInvoiceUsingToken throws GenericErrorResponseException on HTTP success containing errors property', function () use ($baseUrl) {
    // given
    Http::fake(["{$baseUrl}/v1/charge" => Http::response(['errors' => 'Some error'])]);

    // when
    $this->sut->chargeInvoiceUsingToken('some-invoice-id', 'some-token');
})->throws(
    GenericErrorResponseException::class,
    'There was an error during the request.'
);

test('chargeInvoiceUsingToken throws RequestException on HTTP error response', function () use ($baseUrl) {
    // given
    Http::fake(["{$baseUrl}/v1/charge" => Http::response(null, 400)]);

    // when
    $this->sut->chargeInvoiceUsingToken('some-invoice-id', 'some-token');
})->throws(RequestException::class);

test('subscribeWebhook POST to /v1/web_hooks', function () use ($baseUrl) {
    // given
    Http::fake(["{$baseUrl}/v1/web_hooks" => Http::response()]);
    $token = 'some-webhook-token';

    // when
    $this->sut->subscribeWebhook($token);

    // then
    Http::assertSent(static function (Request $request) use ($token) {
        $body = $request->data();

        return data_get($body, 'event') === 'all' &&
            data_get($body, 'url') === route('webhooks.gateway', ['gateway' => 'iugu']) &&
            data_get($body, 'authorization') === $token;
    });
});

test('getGatewayIdentifier returns expected identifier', function () {
    // when
    $identifier = $this->sut->getGatewayIdentifier();

    // then
    expect($identifier)->toEqual('iugu');
});
