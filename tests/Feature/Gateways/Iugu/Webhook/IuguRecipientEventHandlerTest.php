<?php

use Illuminate\Support\Facades\Event;
use ValeSaude\PaymentGatewayClient\Events\InvoicePaidViaWebhook;
use ValeSaude\PaymentGatewayClient\Events\RecipientApprovedViaWebhook;
use ValeSaude\PaymentGatewayClient\Events\RecipientDeclinedViaWebhook;
use ValeSaude\PaymentGatewayClient\Gateways\Exceptions\UnexpectedWebhookPayloadException;
use ValeSaude\PaymentGatewayClient\Gateways\Exceptions\WebhookSubjectNotFound;
use ValeSaude\PaymentGatewayClient\Gateways\Iugu\IuguGateway;
use ValeSaude\PaymentGatewayClient\Gateways\Iugu\Webhook\IuguRecipientEventHandler;
use ValeSaude\PaymentGatewayClient\Models\Recipient;
use ValeSaude\PaymentGatewayClient\Models\Webhook;
use ValeSaude\PaymentGatewayClient\Recipient\Enums\RecipientStatus;
use function Pest\Laravel\expectsEvents;

beforeEach(function () {
    $this->gatewayMock = $this->createMock(IuguGateway::class);
    $this->sut = new IuguRecipientEventHandler($this->gatewayMock);

    $this->gatewayMock
        ->method('getGatewayIdentifier')
        ->willReturn('mock');
});

it('handles expected events', function (string $event, bool $expected) {
    // given
    $webhook = Webhook
        ::factory()
        ->create(['event' => $event]);

    // when
    $shouldHandle = $this->sut->shouldHandle($webhook);

    // then
    expect($shouldHandle)->toEqual($expected);
})->with([
    ['referrals.verification', true],
]);

it('throws when request does not have data.account_id', function () {
    // given
    $webhook = Webhook
        ::factory()
        ->withRequest([])
        ->create();

    // when
    $this->sut->handle($webhook);
})->throws(UnexpectedWebhookPayloadException::class, 'Missing required data.account_id property.');

it('throws when there is no recipient with provided id and gateway', function () {
    // given
    $webhook = Webhook
        ::factory()
        ->withRequest(['data' => ['account_id' => 'some-id']])
        ->create();

    // when
    $this->sut->handle($webhook);
})->throws(WebhookSubjectNotFound::class, 'No recipient found with gateway id.');

it('throws when request does not have data.status', function () {
    // given
    $recipient = Recipient::factory()->create();
    $webhook = Webhook
        ::factory()
        ->withRequest(['data' => ['account_id' => $recipient->gateway_id]])
        ->create();

    // when
    $this->sut->handle($webhook);
})->throws(UnexpectedWebhookPayloadException::class, 'Missing required data.status property.');

it('marks recipient as approved and emits RecipientApprovedViaWebhookEvent when data.status is accepted', function () {
    // given
    expectsEvents([RecipientApprovedViaWebhook::class]);
    $recipient = Recipient::factory()->create();
    $webhook = Webhook
        ::factory()
        ->withRequest([
            'data' => [
                'account_id' => $recipient->gateway_id,
                'status' => 'accepted',
            ],
        ])
        ->create();

    // when
    $this->sut->handle($webhook);
    $recipient->refresh();

    // then
    expect($recipient->status->equals(RecipientStatus::APPROVED()))->toBeTrue();
    Event::assertDispatched(InvoicePaidViaWebhook::class);
});

it('marks recipient as declined and emits RecipientDeclinedViaWebhookEvent when data.status is rejected', function () {
    // given
    expectsEvents([RecipientDeclinedViaWebhook::class]);
    $recipient = Recipient::factory()->create();
    $webhook = Webhook
        ::factory()
        ->withRequest([
            'data' => [
                'account_id' => $recipient->gateway_id,
                'status' => 'rejected',
            ],
        ])
        ->create();

    // when
    $this->sut->handle($webhook);
    $recipient->refresh();

    // then
    expect($recipient->status->equals(RecipientStatus::DECLINED()))->toBeTrue();
    Event::assertDispatched(InvoicePaidViaWebhook::class);
});
