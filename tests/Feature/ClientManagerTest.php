<?php

use Mockery\MockInterface;
use ValeSaude\PaymentGatewayClient\Client;
use ValeSaude\PaymentGatewayClient\ClientManager;
use ValeSaude\PaymentGatewayClient\Contracts\ClientInterface;
use ValeSaude\PaymentGatewayClient\FakeClient;
use ValeSaude\PaymentGatewayClient\Gateways\Fake\FakeGateway;
use ValeSaude\PaymentGatewayClient\Tests\Dummies\DummyGateway;

beforeEach(function () {
    config()->set('payment-gateway-client.default_gateway', 'dummy');
    config()->set('payment-gateway-client.gateways.dummy', DummyGateway::class);
});

afterEach(function () {
    ClientManager::clearResolvedInstances();
});

test('resolve method returns client using default gateway when slug is not provided', function () {
    // when
    $client = ClientManager::resolve();

    // then
    expect($client)->toBeInstanceOf(Client::class)
        ->and($client->getGateway())->toBeInstanceOf(DummyGateway::class);
});

test('resolve method returns client using specified gateway when slug is provided', function () {
    // given
    config()->set('payment-gateway-client.default_gateway', 'some-gateway');

    // when
    $client = ClientManager::resolve('dummy');

    // then
    expect($client)->toBeInstanceOf(Client::class)
        ->and($client->getGateway())->toBeInstanceOf(DummyGateway::class);
});

test('fake method returns FakeClient instance, replacing resolved client instance', function () {
    // given
    $previouslyResolvedClient = ClientManager::resolve();

    // when
    $fakeClient = ClientManager::fake();
    $resolvedClient = ClientManager::resolve();

    // then
    expect($previouslyResolvedClient)->toBeInstanceOf(Client::class)
        ->and($previouslyResolvedClient->getGateway())->toBeInstanceOf(DummyGateway::class)
        ->and($fakeClient)->toBeInstanceOf(FakeClient::class)
        ->and($fakeClient->getGateway())->toBeInstanceOf(FakeGateway::class)
        ->and($resolvedClient === $fakeClient)->toBeTrue();
});

test('fake method replaces default instance with fake instance when slug is not provided', function () {
    // given
    $previouslyResolvedClient = resolve(ClientInterface::class);

    // when
    ClientManager::fake();
    $resolvedClient = resolve(ClientInterface::class);

    // then
    expect($previouslyResolvedClient)->toBeInstanceOf(Client::class)
        ->and($previouslyResolvedClient->getGateway())->toBeInstanceOf(DummyGateway::class)
        ->and($resolvedClient)->toBeInstanceOf(FakeClient::class);
});

test('mock method returns MockObject instance, replacing resolved client instance', function () {
    // given
    $previouslyResolvedClient = ClientManager::resolve();

    // when
    $mockClient = ClientManager::mock();
    $resolvedClient = ClientManager::resolve();

    // then
    expect($previouslyResolvedClient)->toBeInstanceOf(Client::class)
        ->and($previouslyResolvedClient->getGateway())->toBeInstanceOf(DummyGateway::class)
        ->and($mockClient)->toBeInstanceOf(MockInterface::class)
        ->and($resolvedClient === $mockClient)->toBeTrue();
});

test('mock method replaces default instance with mock instance when slug is not provided', function () {
    // given
    $previouslyResolvedClient = resolve(ClientInterface::class);

    // when
    ClientManager::mock();
    $resolvedClient = resolve(ClientInterface::class);

    // then
    expect($previouslyResolvedClient)->toBeInstanceOf(Client::class)
        ->and($previouslyResolvedClient->getGateway())->toBeInstanceOf(DummyGateway::class)
        ->and($resolvedClient)->toBeInstanceOf(MockInterface::class);
});
