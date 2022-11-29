<?php

use ValeSaude\PaymentGatewayClient\Client;
use ValeSaude\PaymentGatewayClient\Customer\GatewayPaymentMethodDTO;
use ValeSaude\PaymentGatewayClient\Exceptions\UnsupportedFeatureException;
use ValeSaude\PaymentGatewayClient\Gateways\Enums\GatewayFeature;
use ValeSaude\PaymentGatewayClient\Models\Customer;
use ValeSaude\PaymentGatewayClient\Models\PaymentMethod;
use ValeSaude\PaymentGatewayClient\Tests\Concerns\HasCustomerHelperMethodsTrait;
use ValeSaude\PaymentGatewayClient\Tests\Concerns\MocksGatewayMethodsTrait;

uses(HasCustomerHelperMethodsTrait::class, MocksGatewayMethodsTrait::class);

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
        ->expects($this->once())
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
        ->expects($this->never())
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
        ->expects($this->once())
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
        ->expects($this->never())
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
        ->expects($this->once())
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
        ->method('createPaymentMethod')
        ->willReturnCallback(static fn () => new GatewayPaymentMethodDTO($expectedId, $creditCard));

    // when
    $paymentMethod = $this->sut->createPaymentMethod($customer, $data, true);

    // then
    expect($paymentMethod->is_default)->toBeTrue()
        ->and($paymentMethod->description)->toEqual($data->description)
        ->and($previouslyDefaultPaymentMethod->refresh()->is_default)->toBeFalse();
});
