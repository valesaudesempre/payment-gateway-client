<?php

use Carbon\CarbonImmutable;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use ValeSaude\LaravelValueObjects\Money;
use ValeSaude\PaymentGatewayClient\Gateways\Enums\GatewayFeature;
use ValeSaude\PaymentGatewayClient\Gateways\Exceptions\InvalidPaymentTokenException;
use ValeSaude\PaymentGatewayClient\Gateways\Exceptions\TransactionDeclinedException;
use ValeSaude\PaymentGatewayClient\Gateways\Iugu\Exceptions\GenericErrorResponseException;
use ValeSaude\PaymentGatewayClient\Gateways\Iugu\IuguGateway;
use ValeSaude\PaymentGatewayClient\Gateways\Iugu\Utils\IuguPriceRange;
use ValeSaude\PaymentGatewayClient\Invoice\Builders\InvoiceBuilder;
use ValeSaude\PaymentGatewayClient\Invoice\Enums\InvoicePaymentMethod;
use ValeSaude\PaymentGatewayClient\Invoice\Enums\InvoiceStatus;
use ValeSaude\PaymentGatewayClient\Invoice\InvoiceItemDTO;
use ValeSaude\PaymentGatewayClient\Models\Recipient;
use ValeSaude\PaymentGatewayClient\Recipient\Enums\RecipientStatus;
use ValeSaude\PaymentGatewayClient\Tests\Concerns\HasCustomerHelperMethodsTrait;
use ValeSaude\PaymentGatewayClient\Tests\Concerns\HasRecipientHelperMethodsTrait;

uses(
    HasCustomerHelperMethodsTrait::class,
    HasRecipientHelperMethodsTrait::class,
);

expect()->extend('toHaveAuthorization', function (string $token) {
    /** @var Request $request */
    $request = $this->value;

    return expect(head($request->header('Authorization')))->toBe('Basic '.base64_encode($token));
});

$baseUrl = 'https://some.url';

beforeEach(fn () => $this->sut = new IuguGateway($baseUrl, 'some-api-key', false));

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

test('deletePaymentMethod DELETE to v1/customers/{customer_id}/payment_methods/{payment_method_id}', function () use ($baseUrl) {
    // given
    $customerId = 'some-customer-id';
    $paymentMethodId = 'some-payment-method-id';
    Http::fake([
        "{$baseUrl}/v1/customers/{$customerId}/payment_methods/{$paymentMethodId}" => Http::response(),
    ]);

    // when
    $this->sut->deletePaymentMethod($customerId, $paymentMethodId);

    // then
    Http::assertSent(static function (Request $request) use ($paymentMethodId, $customerId, $baseUrl) {
        return $request->url() === "{$baseUrl}/v1/customers/{$customerId}/payment_methods/{$paymentMethodId}";
    });
});

test('deletePaymentMethod throws RequestException on HTTP error response', function () use ($baseUrl) {
    // given
    $customerId = 'some-customer-id';
    $paymentMethodId = 'some-payment-method-id';
    Http::fake([
        "{$baseUrl}/v1/customers/{$customerId}/payment_methods/{$paymentMethodId}" => Http::response(null, 400),
    ]);

    // when
    $this->sut->deletePaymentMethod($customerId, $paymentMethodId);
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
    $this->sut->chargeInvoiceUsingPaymentMethod($invoiceId, $customerId, $paymentMethodId, 2);

    // then
    Http::assertSent(static function (Request $request) use ($invoiceId, $customerId, $paymentMethodId) {
        $body = $request->data();

        return data_get($body, 'invoice_id') === $invoiceId &&
            data_get($body, 'customer_id') === $customerId &&
            data_get($body, 'customer_payment_method_id') === $paymentMethodId &&
            data_get($body, 'months') === 2;
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
    $this->sut->chargeInvoiceUsingToken($invoiceId, $token, 3);

    // then
    Http::assertSent(static function (Request $request) use ($invoiceId, $token) {
        $body = $request->data();

        return data_get($body, 'invoice_id') === $invoiceId &&
            data_get($body, 'token') === $token &&
            data_get($body, 'months') === 3;
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

test('chargeInvoiceUsingToken throws InvalidPaymentTokenException on HTTP success containing "token não é válido" message', function () use ($baseUrl) {
    // given
    Http::fake(["{$baseUrl}/v1/charge" => Http::response(['errors' => 'token não é válido'])]);

    // when
    $this->sut->chargeInvoiceUsingToken('some-invoice-id', 'some-token');
})->throws(
    InvalidPaymentTokenException::class,
    'Invalid payment token.'
);

test('chargeInvoiceUsingToken throws InvalidPaymentTokenException on HTTP success containing "Esse token já foi usado." message', function () use ($baseUrl) {
    // given
    Http::fake(["{$baseUrl}/v1/charge" => Http::response(['errors' => 'Esse token já foi usado.'])]);

    // when
    $this->sut->chargeInvoiceUsingToken('some-invoice-id', 'some-token');
})->throws(
    InvalidPaymentTokenException::class,
    'Token already used.'
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

test('refundInvoice POST to v1/invoices/{invoice_id}/refund without partial value when refundValue is not provided', function () use ($baseUrl) {
    // given
    Http::fake(["{$baseUrl}/v1/invoices/some-invoice-id/refund" => Http::response()]);

    // when
    $this->sut->refundInvoice('some-invoice-id');

    // Then
    Http::assertSent(static function (Request $request) {
        return empty($request->data());
    });
});

test('refundInvoice POST to v1/invoices/{invoice_id}/refund with partial value when refundValue is provided', function () use ($baseUrl) {
    // given
    Http::fake(["{$baseUrl}/v1/invoices/some-invoice-id/refund" => Http::response()]);

    // when
    $this->sut->refundInvoice('some-invoice-id', new Money(100));

    // Then
    Http::assertSent(static function (Request $request) {
        $partialValue = $request->data()['partial_value_refund_cents'];

        return $partialValue === 100;
    });
});

test('refundInvoice throws RequestException on HTTP error response', function () use ($baseUrl) {
    // given
    Http::fake(["{$baseUrl}/v1/invoices/some-invoice-id/refund" => Http::response(null, 400)]);

    // when
    $this->sut->refundInvoice('some-invoice-id');
})->throws(RequestException::class);

test('createRecipient POST to v1/marketplace/create_account then POST to v1/accounts/{account_id}/request_verification and return GatewayRecipientDTO on success', function () use ($baseUrl) {
    // given
    $data = $this->createRecipientDTO();
    $data->gatewaySpecificData = $data
        ->gatewaySpecificData
        ->set('price_range', IuguPriceRange::MORE_THAN_500)
        ->set('physical_products', false)
        ->set('business_type', 'Some business type');
    $expectedId = (string) Str::uuid();
    Http::fake([
        "{$baseUrl}/v1/marketplace/create_account" => Http::response([
            'account_id' => $expectedId,
            'live_api_token' => 'live-api-token',
            'test_api_token' => 'test-api-token',
            'user_token' => 'user-token',
        ]),
        "{$baseUrl}/v1/accounts/{$expectedId}/request_verification" => Http::response(),
    ]);

    // when
    $recipient = $this->sut->createRecipient($data);

    // then
    Http::assertSentInOrder([
        static function (Request $request) {
            expect($request)->toHaveAuthorization('some-api-key');

            return true;
        },
        static function (Request $request) use ($data) {
            $gatewaySpecificData = $data->gatewaySpecificData;
            $address = $data->address;
            $bankAccount = $data->bankAccount;

            expect($request)->toHaveAuthorization('user-token')
                ->and($request->data())
                ->toHaveKey('price_range', $gatewaySpecificData->get('price_range'))
                ->toHaveKey('physical_products', $gatewaySpecificData->get('physical_products'))
                ->toHaveKey('business_type', $gatewaySpecificData->get('business_type'))
                ->toHaveKey('telephone', (string) $data->phone)
                ->toHaveKey('automatic_transfer', $data->automaticWithdrawal)
                ->toHaveKey('person_type', 'Pessoa Jurídica')
                ->toHaveKey('cnpj', $data->document->getNumber())
                ->toHaveKey('cep', (string) $address->getZipCode())
                ->toHaveKey('address', "{$address->getStreet()}, {$address->getNumber()}")
                ->toHaveKey('district', $address->getDistrict())
                ->toHaveKey('city', $address->getCity())
                ->toHaveKey('state', $address->getState())
                ->toHaveKey('resp_name', $data->representative->name)
                ->toHaveKey('resp_cpf', $data->representative->document->getNumber())
                ->toHaveKey('bank', (string) $bankAccount->getBank())
                ->toHaveKey('bank_ag', $bankAccount->getAgencyFormatted())
                ->toHaveKey('account_type', 'Corrente')
                ->toHaveKey('bank_cc', $bankAccount->getAccountFormatted());

            return true;
        },
    ]);
    expect($recipient->id)->toEqual($expectedId)
        ->and($recipient->status->equals(RecipientStatus::PENDING()))->toBeTrue()
        ->and($recipient->gatewaySpecificData->toArray())
        ->toHaveKey('live_api_token', 'live-api-token')
        ->toHaveKey('test_api_token', 'test-api-token')
        ->toHaveKey('user_token', 'user-token');
});

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

test('getSupportedFeatures returns all features when test mode is disabled', function () {
    // givem
    $gateway = new IuguGateway($this->baseUrl, 'some-api-key', false);

    // when
    $supportedFeatures = $gateway->getSupportedFeatures();

    // then
    expect($supportedFeatures)->toEqualCanonicalizing(GatewayFeature::cases());
});

test('getSupportedFeatures returns all features except RECIPIENT when test mode is enabled', function () {
    // givem
    $gateway = new IuguGateway($this->baseUrl, 'some-api-key', true);

    // when
    $supportedFeatures = $gateway->getSupportedFeatures();

    // then
    expect($supportedFeatures)
        ->toEqualCanonicalizing(array_diff(GatewayFeature::cases(), [GatewayFeature::RECIPIENT()]));
});
