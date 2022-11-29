<?php

namespace ValeSaude\PaymentGatewayClient;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use ValeSaude\PaymentGatewayClient\Gateways\Contracts\GatewayInterface;

class PaymentGatewayClientServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('payment-gateway-client')
            ->hasMigrations(
                '2022_11_17_190221_create_payment_gateway_customers_table'
            )
            ->runsMigrations(true);
    }

    public function packageRegistered(): void
    {
        $this->app->bind(GatewayInterface::class, static function () {
            $defaultGateway = config('payment-gateway-client.default_gateway');
            $class = config("payment-gateway-client.gateways.{$defaultGateway}");

            return resolve($class);
        });
    }
}
