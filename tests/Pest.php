<?php

use Illuminate\Http\Client\Request;
use ValeSaude\PaymentGatewayClient\Tests\FeatureTestCase;
use ValeSaude\PaymentGatewayClient\Tests\TestCase;

uses(TestCase::class)->in(__DIR__.'/Unit');
uses(FeatureTestCase::class)->in(__DIR__.'/Feature');

expect()->extend('toHaveInRequestPayload', function (string $key, $value) {
    /** @var Request $request */
    $request = $this->value;

    expect(data_get($request->data(), $key))->toBe($value);
});
