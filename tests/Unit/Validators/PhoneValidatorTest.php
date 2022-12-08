<?php

use ValeSaude\PaymentGatewayClient\Validators\PhoneValidator;

beforeEach(fn () => $this->sut = new PhoneValidator());

test('sanitize removes unwanted Phone characters', function () {
    // given
    $phoneWithMask = '(26) 66666-6666';

    // when
    $sanitizedPhone = $this->sut->sanitize($phoneWithMask);

    // then
    expect($sanitizedPhone)->toEqual('26666666666');
});

test('validate returns correctly validates Phones', function (string $phone, bool $expected) {
    // when
    $isPhoneValid = $this->sut->validate($phone);

    // then
    expect($isPhoneValid)->toEqual($expected);
})->with([
    '10 digits Phone without mask' => [
        '2666666666',
        true,
    ],
    '11 digits Phone without mask' => [
        '26666666666',
        true,
    ],
    'valid Phone with mask' => [
        '(11) 26666-6666',
        true,
    ],
    'invalid Phone' => [
        'some random string',
        false,
    ],
    'Phone with leading characters' => [
        '99926666666666',
        false,
    ],
    'Phone with trailing characters' => [
        '26666666666999',
        false,
    ],
]);
