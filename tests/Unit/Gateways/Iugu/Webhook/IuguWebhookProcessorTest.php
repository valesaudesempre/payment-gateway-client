<?php

use ValeSaude\PaymentGatewayClient\Gateways\Iugu\Webhook\IuguInvoiceEventHandler;
use ValeSaude\PaymentGatewayClient\Gateways\Iugu\Webhook\IuguRecipientEventHandler;
use ValeSaude\PaymentGatewayClient\Gateways\Iugu\Webhook\IuguWebhookProcessor;
use ValeSaude\PaymentGatewayClient\Models\Webhook;
use ValeSaude\PaymentGatewayClient\Tests\Concerns\MocksHttpRequestObjectTrait;

uses(MocksHttpRequestObjectTrait::class);

beforeEach(function () {
    $this->sut = new IuguWebhookProcessor();
    $this->iuguInvoiceEventHandlerMock = $this->createMock(IuguInvoiceEventHandler::class);
    $this->iuguRecipientEventHandlerMock = $this->createMock(IuguRecipientEventHandler::class);
    $this->instance(IuguInvoiceEventHandler::class, $this->iuguInvoiceEventHandlerMock);
    $this->instance(IuguRecipientEventHandler::class, $this->iuguRecipientEventHandlerMock);
});

test('authenticate method returns true when request authorization matches hashed webhook_token', function () {
    // given
    $token = 'some-token';
    $request = $this->createFakeRequestObject([], ['Authorization' => bcrypt($token)]);
    config(['services.iugu.webhook_token' => $token]);

    // when
    $passes = $this->sut->authenticate($request);

    // then
    expect($passes)->toBeTrue();
});

test('process method throws when event param is not present', function () {
    // given
    $webhook = Webhook
        ::factory()
        ->withRequest([])
        ->make();

    // when
    $this->sut->process($webhook);
})->throws(UnexpectedValueException::class, 'The webhook event name is invalid.');

test('process method returns true when event is handled by a handler', function () {
    // given
    $event = 'some-event';
    $webhook = Webhook
        ::factory()
        ->withRequest(['event' => $event])
        ->make();
    $this->iuguInvoiceEventHandlerMock
        ->expects($this->once())
        ->method('shouldHandle')
        ->with($webhook)
        ->willReturn(true);
    $this->iuguInvoiceEventHandlerMock
        ->expects($this->once())
        ->method('handle')
        ->with($webhook);

    // when
    $processed = $this->sut->process($webhook);

    // then
    expect($processed)->toBeTrue();
});

test('process method returns false when event is not handled by any handlers', function () {
    // given
    $event = 'some-event';
    $webhook = Webhook
        ::factory()
        ->withRequest(['event' => $event])
        ->make();
    $this->iuguInvoiceEventHandlerMock
        ->expects($this->once())
        ->method('shouldHandle')
        ->with($webhook)
        ->willReturn(false);
    $this->iuguInvoiceEventHandlerMock
        ->expects($this->never())
        ->method('handle');
    $this->iuguRecipientEventHandlerMock
        ->expects($this->once())
        ->method('shouldHandle')
        ->with($webhook)
        ->willReturn(false);
    $this->iuguRecipientEventHandlerMock
        ->expects($this->never())
        ->method('handle');

    // when
    $processed = $this->sut->process($webhook);

    // then
    expect($processed)->toBeFalse();
});

test('process method throws when a handler throw', function () {
    // given
    $event = 'some-event';
    $webhook = Webhook
        ::factory()
        ->withRequest(['event' => $event])
        ->make();
    $this->iuguInvoiceEventHandlerMock
        ->method('shouldHandle')
        ->willThrowException(new Exception('Some exception.'));

    // when
    $processed = $this->sut->process($webhook);

    // then
    expect($processed)->toBeFalse();
})->throws(Exception::class, 'Some exception.');
