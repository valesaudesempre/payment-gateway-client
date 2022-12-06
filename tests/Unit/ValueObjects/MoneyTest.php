<?php

use ValeSaude\PaymentGatewayClient\Casts\MoneyCast;
use ValeSaude\PaymentGatewayClient\ValueObjects\Money;

test('getCents returns the value in cents', function () {
    // given
    $instance = new Money(12345);

    // when
    $cents = $instance->getCents();

    // then
    expect($cents)->toEqual(12345);
});

test('toFloat returns the value in cents divided by 100, rounded half up with 2 decimal places', function () {
    // given
    $instance = new Money(12345);

    // when
    $float = $instance->toFloat();

    // then
    expect($float)->toEqual(123.45);
});

test('multiply returns a new instance, containing the value multiplied by the given multiplier', function () {
    // given
    $instance = new Money(1000);

    // when
    $multipliedBy3 = $instance->multiply(3);

    // then
    expect($instance)->not->toBe($multipliedBy3)
        ->and($instance->getCents())->toEqual(1000)
        ->and($multipliedBy3->getCents())->toEqual(3000);
});

test('castUsing returns an instance of MoneyCast', function () {
    // when
    $cast = Money::castUsing([]);

    // then
    expect($cast)->toBeInstanceOf(MoneyCast::class);
});

test('zero returns an instance of Money with 0 as value', function () {
    // when
    $instance = Money::zero();

    // then
    expect($instance->getCents())->toEqual(0);
});
