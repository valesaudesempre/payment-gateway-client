<?php

namespace ValeSaude\PaymentGatewayClient\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use ValeSaude\PaymentGatewayClient\Enums\DocumentType;
use ValeSaude\PaymentGatewayClient\Models\Recipient;
use ValeSaude\PaymentGatewayClient\Recipient\Enums\BankAccountType;
use ValeSaude\PaymentGatewayClient\Recipient\Enums\RecipientStatus;
use ValeSaude\PaymentGatewayClient\ValueObjects\Address;
use ValeSaude\PaymentGatewayClient\ValueObjects\Bank;
use ValeSaude\PaymentGatewayClient\ValueObjects\BankAccount;
use ValeSaude\PaymentGatewayClient\ValueObjects\Document;
use ValeSaude\PaymentGatewayClient\ValueObjects\JsonObject;
use ValeSaude\PaymentGatewayClient\ValueObjects\Phone;
use ValeSaude\PaymentGatewayClient\ValueObjects\ZipCode;

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
