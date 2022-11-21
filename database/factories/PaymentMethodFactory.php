<?php

namespace ValeSaude\PaymentGatewayClient\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use ValeSaude\PaymentGatewayClient\Models\Customer;
use ValeSaude\PaymentGatewayClient\Models\PaymentMethod;
use ValeSaude\PaymentGatewayClient\ValueObjects\CreditCard;
use ValeSaude\PaymentGatewayClient\ValueObjects\Month;
use ValeSaude\PaymentGatewayClient\ValueObjects\PositiveInteger;

class PaymentMethodFactory extends Factory
{
    protected $model = PaymentMethod::class;

    public function definition(): array
    {
        return [
            'gateway_id' => $this->faker->word(),
            'gateway_slug' => 'mock',
            'description' => $this->faker->text(),
            'is_default' => false,
            'card' => new CreditCard(
                $this->faker->name(),
                'XXXX-XXXX-XXXX-1234',
                'visa',
                new Month(12),
                new PositiveInteger(2030)
            ),
            'customer_id' => Customer::factory(),
        ];
    }

    public function asDefault(): self
    {
        return $this->state(['is_default' => true]);
    }
}
