<?php

namespace ValeSaude\PaymentGatewayClient\Models;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use ValeSaude\PaymentGatewayClient\Database\Factories\WebhookFactory;
use ValeSaude\PaymentGatewayClient\Models\Concerns\GeneratesUUIDOnInitializeTrait;
use ValeSaude\PaymentGatewayClient\Models\Concerns\HasDefaultAttributesOnInitializeTrait;
use ValeSaude\PaymentGatewayClient\Models\Concerns\HasGatewayIdTrait;
use ValeSaude\PaymentGatewayClient\ValueObjects\JsonObject;
use ValeSaude\PaymentGatewayClient\Webhook\WebhookStatus;

/**
 * @property string|null     $event
 * @property JsonObject      $request
 * @property JsonObject      $headers
 * @property WebhookStatus   $status
 * @property string|null     $error_message
 * @property CarbonImmutable $requested_at
 * @property CarbonImmutable $responded_at
 */
class Webhook extends AbstractModel
{
    use GeneratesUUIDOnInitializeTrait;
    use HasFactory;
    use HasGatewayIdTrait;
    use HasDefaultAttributesOnInitializeTrait;
    use SoftDeletes;

    protected $table = 'payment_gateway_webhooks';

    /**
     * @var array<string, string|class-string>
     */
    protected $casts = [
        'request' => JsonObject::class,
        'headers' => JsonObject::class,
        'status' => WebhookStatus::class,
        'requested_at' => 'immutable_datetime',
    ];

    /**
     * @return array<string, mixed>
     */
    public function getDefaultAttributes(): array
    {
        return ['status' => WebhookStatus::PENDING()];
    }

    public function setSuccess(): void
    {
        $this->status = WebhookStatus::SUCCESS();
        $this->save();
    }

    public function setIgnored(): void
    {
        $this->status = WebhookStatus::IGNORED();
        $this->save();
    }

    public function setError(string $message): void
    {
        $this->status = WebhookStatus::ERROR();
        $this->error_message = $message;
        $this->save();
    }

    /**
     * @param array<string, mixed> $request
     * @param array<string, mixed> $headers
     */
    public static function fromPayload(
        string $gatewaySlug,
        array $request,
        array $headers,
        ?CarbonInterface $requestedAt = null
    ): self {
        return new self([
            'gateway_slug' => $gatewaySlug,
            'request' => new JsonObject($request),
            'headers' => new JsonObject($headers),
            'requested_at' => $requestedAt ?? CarbonImmutable::now(),
        ]);
    }

    protected static function newFactory(): WebhookFactory
    {
        return WebhookFactory::new();
    }
}
