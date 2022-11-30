<?php

namespace ValeSaude\PaymentGatewayClient;

use Mockery;
use Mockery\MockInterface;
use ValeSaude\PaymentGatewayClient\Contracts\ClientInterface;
use ValeSaude\PaymentGatewayClient\Gateways\GatewayManager;

class ClientManager
{
    /** @var array<string, ClientInterface> */
    private static array $instances = [];

    public static function resolve(?string $gatewaySlug = null): ClientInterface
    {
        if (!$gatewaySlug) {
            $gatewaySlug = self::getDefaultGatewaySlug();
        }

        if ($instance = self::$instances[$gatewaySlug] ?? null) {
            return $instance;
        }

        $gateway = GatewayManager::resolveGateway($gatewaySlug);
        $client = new Client($gateway);
        self::swap($gatewaySlug, $client);

        return $client;
    }

    public static function fake(?string $gatewaySlug = null): FakeClient
    {
        if (!$gatewaySlug) {
            $gatewaySlug = self::getDefaultGatewaySlug();
        }

        $client = new FakeClient($gatewaySlug);
        self::swap($gatewaySlug, $client);

        return $client;
    }

    /**
     * @return MockInterface&ClientInterface
     */
    public static function mock(?string $gatewaySlug = null): MockInterface
    {
        if (!$gatewaySlug) {
            $gatewaySlug = self::getDefaultGatewaySlug();
        }

        /** @var MockInterface&ClientInterface $client */
        $client = Mockery::mock(ClientInterface::class);
        self::swap($gatewaySlug, $client);

        return $client;
    }

    public static function swap(string $gatewaySlug, ClientInterface $instance): void
    {
        self::$instances[$gatewaySlug] = $instance;

        if (self::getDefaultGatewaySlug() === $gatewaySlug) {
            self::swapDefaultInstance($instance);
        }
    }

    public static function swapDefaultInstance(ClientInterface $instance): void
    {
        self::$instances[ClientInterface::class] = $instance;
        app()->instance(ClientInterface::class, $instance);
    }

    public static function clearResolvedInstances(): void
    {
        self::$instances = [];
    }

    private static function getDefaultGatewaySlug(): string
    {
        return config('payment-gateway-client.default_gateway');
    }
}
