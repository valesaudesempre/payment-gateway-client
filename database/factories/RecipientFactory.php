<?php

namespace ValeSaude\PaymentGatewayClient\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use ValeSaude\PaymentGatewayClient\Models\Recipient;
use ValeSaude\PaymentGatewayClient\Recipient\Enums\DocumentType;
use ValeSaude\PaymentGatewayClient\ValueObjects\Document;

class RecipientFactory extends Factory
{
    protected $model = Recipient::class;

    public function definition(): array
    {
        return [
            'gateway_id' => $this->faker->uuid(),
            'gateway_slug' => 'mock',
            'name' => $this->faker->name(),
            'document' => new Document($this->faker->cpf(), DocumentType::CNPJ()),
        ];
    }
}
