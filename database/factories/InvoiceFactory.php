<?php

namespace ValeSaude\PaymentGatewayClient\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use ValeSaude\PaymentGatewayClient\Collections\InvoiceSplitRuleCollection;
use ValeSaude\PaymentGatewayClient\Invoice\Collections\InvoicePaymentMethodCollection;
use ValeSaude\PaymentGatewayClient\Invoice\Enums\InvoicePaymentMethod;
use ValeSaude\PaymentGatewayClient\Invoice\Enums\InvoiceStatus;
use ValeSaude\PaymentGatewayClient\Models\Customer;
use ValeSaude\PaymentGatewayClient\Models\Invoice;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'gateway_id' => $this->faker->uuid(),
            'gateway_slug' => 'mock',
            'due_date' => Carbon::now()->addWeek(),
            'max_installments' => $this->faker->numberBetween(1, 12),
            'status' => InvoiceStatus::PENDING(),
            'available_payment_methods' => new InvoicePaymentMethodCollection(InvoicePaymentMethod::cases()),
            'splits' => new InvoiceSplitRuleCollection(),
            'customer_id' => Customer::factory(),
        ];
    }
}
