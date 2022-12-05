<?php

use Illuminate\Support\Facades\Hash;
use ValeSaude\PaymentGatewayClient\Gateways\Iugu\IuguGateway;
use function Pest\Laravel\artisan;
use function Pest\Laravel\instance;
use function PHPUnit\Framework\callback;
use function PHPUnit\Framework\once;

it('calls IuguGateway::subscribeWebhook method', function () {
    // given
    $token = 'some-webhook-token';
    config(['services.iugu.webhook_token' => $token]);
    $iuguGatewayMock = $this->createMock(IuguGateway::class);
    instance(IuguGateway::class, $iuguGatewayMock);
    $iuguGatewayMock->expects(once())
        ->method('subscribeWebhook')
        ->with(
            callback(fn (string $hashedToken) => Hash::check($token, $hashedToken))
        );

    // when
    $response = artisan('gateway:subscribe-iugu-webhooks');

    // then
    $response->assertSuccessful();
});
