<?php

namespace ValeSaude\PaymentGatewayClient\Database\Factories;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use ValeSaude\LaravelValueObjects\JsonObject;
use ValeSaude\PaymentGatewayClient\Models\Webhook;
use ValeSaude\PaymentGatewayClient\Webhook\WebhookStatus;

class WebhookFactory extends Factory
{
    protected $model = Webhook::class;

    public function definition(): array
    {
        return [
            'gateway_id' => $this->faker->uuid(),
            'gateway_slug' => 'mock',
            'request' => JsonObject::empty(),
            'headers' => JsonObject::empty(),
            'status' => WebhookStatus::SUCCESS(),
            'requested_at' => CarbonImmutable::now(),
        ];
    }

    public function withRequest(array $request): self
    {
        return $this->state(['request' => new JsonObject($request)]);
    }
}
