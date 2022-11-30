<?php

namespace ValeSaude\PaymentGatewayClient;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use ValeSaude\PaymentGatewayClient\Contracts\ClientInterface;
use ValeSaude\PaymentGatewayClient\Gateways\Contracts\GatewayInterface;
use ValeSaude\PaymentGatewayClient\Gateways\Iugu\IuguGateway;

class PaymentGatewayClientServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('payment-gateway-client')
            ->hasConfigFile('payment-gateway-client')
            ->hasMigrations(
                '2022_11_17_190221_create_payment_gateway_customers_table',
                '2022_11_18_180000_create_payment_gateway_payment_methods_table',
                '2022_11_21_174730_create_payment_gateway_recipients_table',
                '2022_11_21_174731_create_payment_gateway_invoices_table',
                '2022_11_21_174732_create_payment_gateway_invoice_items_table',
                '2022_11_24_173132_create_payment_gateway_webhooks_table'
            )
            ->runsMigrations(true)
            ->hasRoutes('webhooks');
    }

    public function packageRegistered(): void
    {
        $this->app->bind(GatewayInterface::class, static function (): GatewayInterface {
            $defaultGateway = config('payment-gateway-client.default_gateway');
            $class = config("payment-gateway-client.gateways.{$defaultGateway}");

            return resolve($class);
        });

        $this->app->bind(IuguGateway::class, static function (): IuguGateway {
            return new IuguGateway(
                config('services.iugu.base_url', 'https://api.iugu.com'),
                config('services.iugu.api_key')
            );
        });

        $this->app->bind(ClientInterface::class, static function (): ClientInterface {
            return ClientManager::resolve();
        });
    }
}
