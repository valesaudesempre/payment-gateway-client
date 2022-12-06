<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use ValeSaude\PaymentGatewayClient\Events\InvoiceCanceledViaWebhook;
use ValeSaude\PaymentGatewayClient\Events\InvoicePaidViaWebhook;
use ValeSaude\PaymentGatewayClient\Events\InvoiceRefundedViaWebhook;
use ValeSaude\PaymentGatewayClient\Gateways\Exceptions\UnexpectedWebhookPayloadException;
use ValeSaude\PaymentGatewayClient\Gateways\Exceptions\WebhookSubjectNotFound;
use ValeSaude\PaymentGatewayClient\Gateways\Iugu\IuguGateway;
use ValeSaude\PaymentGatewayClient\Gateways\Iugu\Webhook\IuguInvoiceEventHandler;
use ValeSaude\PaymentGatewayClient\Invoice\Collections\GatewayInvoiceItemDTOCollection;
use ValeSaude\PaymentGatewayClient\Invoice\Enums\InvoiceStatus;
use ValeSaude\PaymentGatewayClient\Invoice\GatewayInvoiceDTO;
use ValeSaude\PaymentGatewayClient\Models\Invoice;
use ValeSaude\PaymentGatewayClient\Models\Webhook;
use function Pest\Laravel\expectsEvents;
use function PHPUnit\Framework\once;

beforeEach(function () {
    $this->gatewayMock = $this->createMock(IuguGateway::class);
    $this->sut = new IuguInvoiceEventHandler($this->gatewayMock);

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
    ['invoice.created', true],
    ['invoice.status_changed', true],
    ['invoice.refund', true],
    ['invoice.payment_failed', true],
    ['invoice.due', true],
    ['invoice.some_other_event', false],
]);

it('throws when request does not have data.id', function () {
    // given
    $webhook = Webhook
        ::factory()
        ->withRequest([])
        ->create();

    // when
    $this->sut->handle($webhook);
})->throws(UnexpectedWebhookPayloadException::class, 'Missing required data.id property.');

it('throws when there is no invoice with provided id and gateway', function () {
    // given
    $webhook = Webhook
        ::factory()
        ->withRequest(['data' => ['id' => 'some-id']])
        ->create();

    // when
    $this->sut->handle($webhook);
})->throws(WebhookSubjectNotFound::class, 'No invoice found with gateway id.');

it('throws when request does not have data.status', function () {
    // given
    $invoice = Invoice::factory()->create();
    $webhook = Webhook
        ::factory()
        ->withRequest(['data' => ['id' => $invoice->gateway_id]])
        ->create();

    // when
    $this->sut->handle($webhook);
})->throws(UnexpectedWebhookPayloadException::class, 'Missing required data.status property.');

it('marks invoice as paid and emits InvoicePaidViaWebhookEvent when data.status is paid', function () {
    // given
    expectsEvents([InvoicePaidViaWebhook::class]);
    $invoice = Invoice::factory()->create();
    $webhook = Webhook
        ::factory()
        ->withRequest([
            'data' => [
                'id' => $invoice->gateway_id,
                'status' => 'paid',
            ],
        ])
        ->create();
    $paidAt = Carbon::today()->subDay();
    $this->gatewayMock
        ->expects(once())
        ->method('getInvoice')
        ->with($invoice->gateway_id)
        ->willReturn(
            new GatewayInvoiceDTO(
                $invoice->gateway_id,
                $invoice->url,
                $invoice->due_date,
                InvoiceStatus::PAID(),
                new GatewayInvoiceItemDTOCollection(),
                2,
                null,
                null,
                $paidAt,
            )
        );

    // when
    $this->sut->handle($webhook);
    $invoice->refresh();

    // then
    expect($invoice->status->equals(InvoiceStatus::PAID()))->toBeTrue()
        ->and($invoice->paid_at->toDateString())->toEqual($paidAt->toDateString())
        ->and($invoice->installments)->toEqual(2);
    Event::assertDispatched(InvoicePaidViaWebhook::class);
});

it('marks invoice as canceled and emits InvoiceCanceledViaWebhook when data.status is canceled', function () {
    // given
    expectsEvents([InvoiceCanceledViaWebhook::class]);
    $invoice = Invoice::factory()->create();
    $webhook = Webhook
        ::factory()
        ->withRequest([
            'data' => [
                'id' => $invoice->gateway_id,
                'status' => 'canceled',
            ],
        ])
        ->create();

    // when
    $this->sut->handle($webhook);
    $invoice->refresh();

    // then
    expect($invoice->status->equals(InvoiceStatus::CANCELED()))->toBeTrue()
        ->and($invoice->canceled_at)->not->toBeNull();
    Event::assertDispatched(InvoiceCanceledViaWebhook::class);
});

it('marks invoice as refunded and emits InvoiceRefundedViaWebhook when data.status is refunded', function () {
    // given
    expectsEvents([InvoiceRefundedViaWebhook::class]);
    $invoice = Invoice
        ::factory()
        ->paid()
        ->create();
    $webhook = Webhook
        ::factory()
        ->withRequest([
            'data' => [
                'id' => $invoice->gateway_id,
                'status' => 'refunded',
            ],
        ])
        ->create();

    // when
    $this->sut->handle($webhook);
    $invoice->refresh();

    // then
    expect($invoice->status->equals(InvoiceStatus::REFUNDED()))->toBeTrue()
        ->and($invoice->refunded_at)->not->toBeNull()
        ->and($invoice->refunded_amount->equals($invoice->total))->toBeTrue();
    Event::assertDispatched(InvoiceRefundedViaWebhook::class);
});

it('sets invoice status when data.status is expired', function () {
    // given
    $invoice = Invoice::factory()->create();
    $webhook = Webhook
        ::factory()
        ->withRequest([
            'data' => [
                'id' => $invoice->gateway_id,
                'status' => 'expired',
            ],
        ])
        ->create();

    // when
    $this->sut->handle($webhook);
    $invoice->refresh();

    // then
    expect($invoice->status->equals(InvoiceStatus::EXPIRED()))->toBeTrue();
});
