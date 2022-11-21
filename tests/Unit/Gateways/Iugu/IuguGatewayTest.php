<?php

use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use ValeSaude\PaymentGatewayClient\Gateways\Iugu\IuguGateway;
use ValeSaude\PaymentGatewayClient\Tests\Concerns\HasCustomerHelperMethodsTrait;

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

test('getGatewayIdentifier returns expected identifier', function () {
    // when
    $identifier = $this->sut->getGatewayIdentifier();

    // then
    expect($identifier)->toEqual('iugu');
});
