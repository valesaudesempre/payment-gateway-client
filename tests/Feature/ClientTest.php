<?php

use ValeSaude\PaymentGatewayClient\Client;
use ValeSaude\PaymentGatewayClient\Customer\CustomerDTO;
use ValeSaude\PaymentGatewayClient\Gateways\Contracts\GatewayInterface;
use ValeSaude\PaymentGatewayClient\Tests\Concerns\HasCustomerHelperMethodsTrait;
use ValeSaude\PaymentGatewayClient\ValueObjects\Address;
use ValeSaude\PaymentGatewayClient\ValueObjects\CPF;
use ValeSaude\PaymentGatewayClient\ValueObjects\Email;
use ValeSaude\PaymentGatewayClient\ValueObjects\ZipCode;

uses(HasCustomerHelperMethodsTrait::class);

beforeEach(function () {
    $this->gatewayMock = $this->createMock(GatewayInterface::class);
    $this->sut = new Client($this->gatewayMock);
});

test('createCustomer method creates a customer using its gateway and returns a Customer instance', function () {
    // given
    $data = $this->createCustomerDTO();
    $expectedId = $this->faker->uuid;
    $this->gatewayMock
        ->method('createCustomer')
        ->with($data)
        ->willReturnCallback(static fn () => $expectedId);

    // when
    $customer = $this->sut->createCustomer($data);

    // then
    expect($customer->gateway_id)->toEqual($expectedId);
    $this->expectCustomerToBeEqualsToData($customer, $data);
});
