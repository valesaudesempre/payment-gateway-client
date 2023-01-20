<?php

namespace ValeSaude\PaymentGatewayClient\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use ValeSaude\LaravelValueObjects\Address;
use ValeSaude\LaravelValueObjects\Bank;
use ValeSaude\LaravelValueObjects\BankAccount;
use ValeSaude\LaravelValueObjects\Document;
use ValeSaude\LaravelValueObjects\Enums\BankAccountType;
use ValeSaude\LaravelValueObjects\Enums\DocumentType;
use ValeSaude\LaravelValueObjects\JsonObject;
use ValeSaude\LaravelValueObjects\Phone;
use ValeSaude\LaravelValueObjects\ZipCode;
use ValeSaude\PaymentGatewayClient\Models\Recipient;
use ValeSaude\PaymentGatewayClient\Recipient\Enums\RecipientStatus;

class RecipientFactory extends Factory
{
    protected $model = Recipient::class;

    public function definition(): array
    {
        return [
            'gateway_id' => $this->faker->uuid(),
            'gateway_slug' => 'mock',
            'name' => $this->faker->name(),
            'document' => new Document($this->faker->cnpj(), DocumentType::CNPJ()),
            'representative_name' => $this->faker->name(),
            'representative_document' => new Document($this->faker->cpf(), DocumentType::CPF()),
            'address' => new Address(
                new ZipCode($this->faker->numerify('########')),
                $this->faker->streetName(),
                $this->faker->buildingNumber(),
                $this->faker->word(),
                $this->faker->city(),
                $this->faker->lexify('??'),
            ),
            'phone' => new Phone($this->faker->numerify('###########')),
            'bank_account' => new BankAccount(
                new Bank($this->faker->randomKey(Bank::BANKS)),
                $this->faker->numerify('####'),
                $this->faker->numerify('#'),
                $this->faker->numerify('########'),
                $this->faker->numerify('#'),
                BankAccountType::CHECKING()
            ),
            'automatic_withdrawal' => true,
            'gateway_specific_data' => JsonObject::empty(),
            'status' => RecipientStatus::APPROVED(),
        ];
    }
}
