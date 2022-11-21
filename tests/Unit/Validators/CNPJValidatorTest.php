<?php

use ValeSaude\PaymentGatewayClient\Validators\CNPJValidator;

beforeEach(fn () => $this->sut = new CNPJValidator());

test('sanitize removes unwanted CNPJ characters', function () {
    // given
    $cnpjWithMask = '75.344.646/0001-95';

    // when
    $sanitizedCnpj = $this->sut->sanitize($cnpjWithMask);

    // then
    expect($sanitizedCnpj)->toEqual('75344646000195');
});

test('validate returns correctly validates CNPJs', function (string $cnpj, bool $expected) {
    // when
    $isCnpjValid = $this->sut->validate($cnpj);

    // then
    expect($isCnpjValid)->toEqual($expected);
})->with([
    'valid CNPJ without mask' => [
        '75344646000195',
        true,
    ],
    'valid CNPJ with mask' => [
        '75.344.646/0001-95',
        true,
    ],
    'invalid CNPJ without mask' => [
        '12345678900001',
        false,
    ],
    'invalid CNPJ with mask' => [
        '12.345.678/9000-1',
        false,
    ],
    'string with less than 14 digits' => [
        '1111111111111',
        false,
    ],
    'CNPJ with repeated characters' => [
        '11111111111111',
        false,
    ],
]);
