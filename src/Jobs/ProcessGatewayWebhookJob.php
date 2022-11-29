<?php

namespace ValeSaude\PaymentGatewayClient\Jobs;

use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;
use ValeSaude\PaymentGatewayClient\Gateways\GatewayManager;
use ValeSaude\PaymentGatewayClient\Models\Webhook;

class ProcessGatewayWebhookJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private string $gateway;
    private CarbonInterface $requestedAt;

    /** @var array<string, mixed> */
    private array $request;

    /** @var array<string, mixed> */
    private array $headers;

    /**
     * @param array<string, mixed> $request
     * @param array<string, mixed> $headers
     */
    public function __construct(string $gateway, array $request, array $headers, CarbonInterface $requestedAt)
    {
        $this->request = $request;
        $this->headers = $headers;
        $this->requestedAt = $requestedAt;
        $this->gateway = $gateway;
    }

    public function handle(): void
    {
        $webhook = Webhook::fromPayload($this->gateway, $this->request, $this->headers, $this->requestedAt);
        $processor = GatewayManager::resolveWebhookProcessor($this->gateway);

        try {
            $processed = $processor->process($webhook);

            if ($processed) {
                $webhook->setSuccess();
            } else {
                $webhook->setIgnored();
            }
        } catch (Throwable $e) {
            $webhook->setError($e->getMessage());
        }
    }

    /**
     * @return string|null
     */
    public function getQueue(): ?string
    {
        return config('payment-gateway-client.webhook_processing_queue');
    }
}
