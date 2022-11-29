<?php

namespace ValeSaude\PaymentGatewayClient\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use ValeSaude\PaymentGatewayClient\Models\Customer;
use ValeSaude\PaymentGatewayClient\ValueObjects\Address;
use ValeSaude\PaymentGatewayClient\ValueObjects\CPF;
use ValeSaude\PaymentGatewayClient\ValueObjects\Email;
use ValeSaude\PaymentGatewayClient\ValueObjects\ZipCode;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'gateway_id' => $this->faker->uuid(),
            'gateway_slug' => $this->faker->slug(),
            'name' => $this->faker->name(),
            'document_number' => new CPF($this->faker->cpf()),
            'email' => new Email($this->faker->unique()->safeEmail()),
            'address' => new Address(
                new ZipCode($this->faker->numerify('########')),
                $this->faker->streetName(),
                $this->faker->buildingNumber(),
                $this->faker->word(),
                $this->faker->city(),
                $this->faker->lexify('??'),
            ),
        ];
    }
}
