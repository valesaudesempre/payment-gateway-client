<?php

namespace ValeSaude\PaymentGatewayClient\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use ValeSaude\LaravelValueObjects\Money;
use ValeSaude\PaymentGatewayClient\Models\Invoice;
use ValeSaude\PaymentGatewayClient\Models\InvoiceItem;

class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    public function definition(): array
    {
        return [
            'gateway_slug' => 'mock',
            'price' => new Money($this->faker->randomNumber(5)),
            'quantity' => $this->faker->randomNumber(1),
            'description' => $this->faker->text(),
            'invoice_id' => Invoice::factory(),
        ];
    }
}
