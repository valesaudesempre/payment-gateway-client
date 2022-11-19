<?php

use ValeSaude\PaymentGatewayClient\Client;
use ValeSaude\PaymentGatewayClient\Gateways\Contracts\GatewayInterface;
use ValeSaude\PaymentGatewayClient\Models\Customer;
use ValeSaude\PaymentGatewayClient\Tests\Concerns\HasCustomerHelperMethodsTrait;

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
        ->expects($this->once())
        ->method('createCustomer')
        ->with($data)
        ->willReturnCallback(static fn () => $expectedId);

    // when
    $customer = $this->sut->createCustomer($data);

    // then
    expect($customer->gateway_id)->toEqual($expectedId);
    $this->expectCustomerToBeEqualsToData($customer, $data);
});

test('updateCustomer method updates a existing customer using its gateway and returns the updated Customer instance', function () {
    // given
    $customer = Customer::factory()->create();
    $data = $this->createCustomerDTO();
    $this->gatewayMock
        ->expects($this->once())
        ->method('updateCustomer')
        ->with($customer->gateway_id, $data);

    // when
    $customer = $this->sut->updateCustomer($customer, $data);

    // then
    $this->expectCustomerToBeEqualsToData($customer, $data);
});
