<?php

use Illuminate\Contracts\Container\BindingResolutionException;
use ValeSaude\PaymentGatewayClient\Customer\CustomerDTO;
use ValeSaude\PaymentGatewayClient\Gateways\Contracts\GatewayInterface;
use ValeSaude\PaymentGatewayClient\Gateways\GatewayManager;

class DummyGateway implements GatewayInterface
{
    public function createCustomer(CustomerDTO $data, string $internalId): string
    {
        throw new BadMethodCallException();
    }

    public function getGatewayIdentifier(): string
    {
        throw new BadMethodCallException();
    }
}

it('resolves a valid gateway using its slug', function () {
    // given
    $dummy = $this->createStub(GatewayInterface::class);
    $dummyClass = get_class($dummy);
    config(['payment-gateway-client.gateways' => ['dummy' => $dummyClass]]);

    // when
    $resolved = GatewayManager::resolve('dummy');

    // then
    expect($resolved)->toBeInstanceOf($dummyClass);
});

it('throws when trying to resolve gateway using invalid slug', function () {
    // given
    config(['payment-gateway-client.gateways' => []]);

    // when
    GatewayManager::resolve('invalid-slug');
})->throws(
    BindingResolutionException::class,
    "Unable to resolve gateway identified by \"invalid-slug\"."
);
