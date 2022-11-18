<?php

use ValeSaude\PaymentGatewayClient\Client;
use ValeSaude\PaymentGatewayClient\Customer\CustomerDTO;
use ValeSaude\PaymentGatewayClient\Gateways\Contracts\GatewayInterface;
use ValeSaude\PaymentGatewayClient\ValueObjects\Address;
use ValeSaude\PaymentGatewayClient\ValueObjects\CPF;
use ValeSaude\PaymentGatewayClient\ValueObjects\Email;
use ValeSaude\PaymentGatewayClient\ValueObjects\ZipCode;

beforeEach(function () {
    $this->gatewayClientMock = $this->createMock(GatewayInterface::class);
    $this->sut = new Client($this->gatewayClientMock);
});

test('createCustomer method creates a customer using its client and returns a Customer instance', function () {
    // given
    $data = new CustomerDTO(
        $this->faker->name,
        new CPF('74406433058'),
        new Email($this->faker->email),
        new Address(
            new ZipCode('01001000'),
            'Some Street',
            1,
            'Some District',
            'Some City',
            'SP'
        )
    );
    $expectedId = $this->faker->uuid;
    $this->gatewayClientMock
        ->method('createCustomer')
        ->with($data)
        ->willReturnCallback(static fn () => $expectedId);

    // when
    $customer = $this->sut->createCustomer($data);

    // then
    expect($customer->gateway_id)->toEqual($expectedId)
        ->and($customer->name)->toEqual($data->name)
        ->and($customer->document_number)->toEqual($data->documentNumber)
        ->and($customer->address)->toEqual($data->address);
});
