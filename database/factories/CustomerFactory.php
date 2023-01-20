<?php

namespace ValeSaude\PaymentGatewayClient\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use ValeSaude\LaravelValueObjects\Address;
use ValeSaude\LaravelValueObjects\Document;
use ValeSaude\LaravelValueObjects\Email;
use ValeSaude\LaravelValueObjects\Enums\DocumentType;
use ValeSaude\LaravelValueObjects\ZipCode;
use ValeSaude\PaymentGatewayClient\Models\Customer;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'gateway_id' => $this->faker->uuid(),
            'gateway_slug' => 'mock',
            'name' => $this->faker->name(),
            'document' => new Document($this->faker->cpf(), DocumentType::CPF()),
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
