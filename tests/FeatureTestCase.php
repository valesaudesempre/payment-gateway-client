<?php

namespace ValeSaude\PaymentGatewayClient\Tests;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

class FeatureTestCase extends TestCase
{
    use LazilyRefreshDatabase;

    protected function getEnvironmentSetUp($app): void
    {
        config(['database.default' => 'testing']);
    }
}
