<?php

namespace ValeSaude\PaymentGatewayClient\Tests;

use Illuminate\Foundation\Testing\WithFaker;
use Orchestra\Testbench\TestCase as BaseTestCase;
use ValeSaude\PaymentGatewayClient\PaymentGatewayClientServiceProvider;

class TestCase extends BaseTestCase
{
    use WithFaker;

    protected function getPackageProviders($app): array
    {
        return [
            PaymentGatewayClientServiceProvider::class,
        ];
    }
}
