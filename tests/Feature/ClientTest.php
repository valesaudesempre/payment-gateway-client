<?php

use ValeSaude\PaymentGatewayClient\Client;
use ValeSaude\PaymentGatewayClient\Gateways\Enums\GatewayFeature;
use ValeSaude\PaymentGatewayClient\Models\Customer;
use ValeSaude\PaymentGatewayClient\Tests\Concerns\HasCustomerHelperMethodsTrait;
use ValeSaude\PaymentGatewayClient\Tests\Concerns\MocksGatewayMethodsTrait;

uses(HasCustomerHelperMethodsTrait::class, MocksGatewayMethodsTrait::class);

beforeEach(function () {
    $this->createGatewayMock();
    $this->sut = new Client($this->gatewayMock);
});

test('createCustomer method creates a customer using its gateway and returns a Customer instance when gateway supports CUSTOMER feature', function () {
    // given
    $data = $this->createCustomerDTO();
    $expectedId = $this->faker->uuid;
    $this->mockGatewaySupportedFeature(GatewayFeature::CUSTOMER());
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

test('createCustomer method creates a Customer internally and returns when gateway does not support CUSTOMER feature', function () {
    // given
    $data = $this->createCustomerDTO();
    $this->mockGatewaySupportedFeature(GatewayFeature::CUSTOMER(), false);
    $this->gatewayMock
        ->expects($this->never())
        ->method('createCustomer');

    // when
    $customer = $this->sut->createCustomer($data);

    // then
    expect($customer->gateway_id)->toBeNull();
    $this->expectCustomerToBeEqualsToData($customer, $data);
});

test('updateCustomer method updates an existing customer using its gateway and returns the updated Customer instance when gateway supports CUSTOMER feature', function () {
    // given
    $customer = Customer::factory()->create();
    $data = $this->createCustomerDTO();
    $this->mockGatewaySupportedFeature(GatewayFeature::CUSTOMER());
    $this->gatewayMock
        ->expects($this->once())
        ->method('updateCustomer')
        ->with($customer->gateway_id, $data);

    // when
    $customer = $this->sut->updateCustomer($customer, $data);

    // then
    $this->expectCustomerToBeEqualsToData($customer, $data);
});

test('updateCustomer method updates an existing Customer internally and returns the updated instance when gateway does not support CUSTOMER feature', function () {
    // given
    $customer = Customer::factory()->create();
    $data = $this->createCustomerDTO();
    $this->mockGatewaySupportedFeature(GatewayFeature::CUSTOMER(), false);
    $this->gatewayMock
        ->expects($this->never())
        ->method('updateCustomer');

    // when
    $customer = $this->sut->updateCustomer($customer, $data);

    // then
    $this->expectCustomerToBeEqualsToData($customer, $data);
});
