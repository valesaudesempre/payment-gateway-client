<?php

namespace ValeSaude\PaymentGatewayClient\Tests;

use Faker\Provider\pt_BR\Company;
use Faker\Provider\pt_BR\Person;
use Illuminate\Foundation\Testing\WithFaker;
use Orchestra\Testbench\TestCase as BaseTestCase;
use ValeSaude\PaymentGatewayClient\PaymentGatewayClientServiceProvider;

class TestCase extends BaseTestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker->addProvider(new Person($this->faker));
        $this->faker->addProvider(new Company($this->faker));
    }

    protected function getPackageProviders($app): array
    {
        return [
            PaymentGatewayClientServiceProvider::class,
        ];
    }
}
