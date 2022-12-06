<?php

use Illuminate\Support\Facades\Queue;
use ValeSaude\PaymentGatewayClient\Gateways\Contracts\WebhookProcessorInterface;
use ValeSaude\PaymentGatewayClient\Jobs\ProcessGatewayWebhookJob;
use function Pest\Laravel\instance;
use function Pest\Laravel\postJson;
use function PHPUnit\Framework\once;

beforeEach(function () {
    Queue::fake();

    $this->processorMock = $this->createMock(WebhookProcessorInterface::class);
    instance(get_class($this->processorMock), $this->processorMock);

    config()->set('payment-gateway-client.webhook_processors.mock', get_class($this->processorMock));
});

it('returns 404 when gateway cannot be resolved', function () {
    // when
    $response = postJson(route('webhooks.gateway', 'invalid-gateway'));

    // then
    expect($response->getStatusCode())->toEqual(404)
        ->and($response->getContent())->toContain('Gateway settings not found.');
});

it('returns 401 when gateway authentication fails', function () {
    // given
    $this->processorMock
        ->expects(once())
        ->method('authenticate')
        ->willReturn(false);

    // when
    $response = postJson(route('webhooks.gateway', 'mock'));

    // then
    expect($response->getStatusCode())->toEqual(401)
        ->and($response->getContent())->toContain('Authentication failed.');
});

it('dispatches ProcessGatewayWebhookJob and returns 200 on success', function () {
    // given
    $expectedQueue = 'fake-queue';
    config()->set('payment-gateway-client.webhook_processing_queue', $expectedQueue);
    $this->processorMock
        ->expects(once())
        ->method('authenticate')
        ->willReturn(true);

    // when
    $response = postJson(route('webhooks.gateway', 'mock'));

    // then
    expect($response->isSuccessful())->toBeTrue();
    Queue::assertPushed(ProcessGatewayWebhookJob::class, function ($job, string $queue) use ($expectedQueue) {
        expect($queue)->toEqual($expectedQueue);

        return true;
    });
});
