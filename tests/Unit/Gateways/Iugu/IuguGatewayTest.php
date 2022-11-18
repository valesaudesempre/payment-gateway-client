<?php

use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use ValeSaude\PaymentGatewayClient\Customer\CustomerDTO;
use ValeSaude\PaymentGatewayClient\Gateways\Iugu\IuguGateway;
use ValeSaude\PaymentGatewayClient\ValueObjects\Address;
use ValeSaude\PaymentGatewayClient\ValueObjects\CPF;
use ValeSaude\PaymentGatewayClient\ValueObjects\Email;
use ValeSaude\PaymentGatewayClient\ValueObjects\ZipCode;

$baseUrl = 'https://some.url';

beforeEach(fn () => $this->sut = new IuguGateway($baseUrl, 'some-api-key'));

test('createCustomer returns external id on success', function () use ($baseUrl) {
    // given
    $expectedExternalId = 'some-external-id';
    $expectedInternalId = 'some-internal-id';
    $data = createCustomerDTO();
    Http::fake(["{$baseUrl}/v1/customers" => Http::response(['id' => $expectedExternalId])]);

    // when
    $id = $this->sut->createCustomer($data, $expectedInternalId);

    // then
    expect($id)->toEqual($expectedExternalId);
    Http::assertSent(static function (Request $request) use ($expectedInternalId, $data) {
        $body = $request->data();

        return data_get($body, 'email') === (string) $data->email &&
            data_get($body, 'name') === $data->name &&
            data_get($body, 'cpf_cnpj') === (string) $data->documentNumber &&
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
    $data = createCustomerDTO();
    Http::fake(["{$baseUrl}/v1/customers" => Http::response(null, 400)]);

    // when
    $this->sut->createCustomer($data, 'some-internal-id');
})->throws(RequestException::class);

test('getGatewayIdentifier returns expected identifier', function () {
    // when
    $identifier = $this->sut->getGatewayIdentifier();

    // then
    expect($identifier)->toEqual('iugu');
});

function createCustomerDTO(): CustomerDTO
{
    return new CustomerDTO(
        'Some Name',
        new CPF('74406433058'),
        new Email('some@mail.com'),
        new Address(
            new ZipCode('01001000'),
            'Some Street',
            1,
            'Some District',
            'Some City',
            'SP'
        )
    );
}
