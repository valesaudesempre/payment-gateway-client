<?php

use ValeSaude\PaymentGatewayClient\Validators\ZipCodeValidator;

beforeEach(fn () => $this->sut = new ZipCodeValidator());

test('sanitize removes unwanted ZipCode characters', function () {
    // given
    $zipCodeWithMask = '01001-000';

    // when
    $sanitizedZipCode = $this->sut->sanitize($zipCodeWithMask);

    // then
    expect($sanitizedZipCode)->toEqual('01001000');
});

test('validate returns correctly validates ZipCodes', function (string $zipCode, bool $expected) {
    // when
    $isZipCodeValid = $this->sut->validate($zipCode);

    // then
    expect($isZipCodeValid)->toEqual($expected);
})->with([
    'valid ZipCode without mask' => [
        '01001000',
        true,
    ],
    'valid ZipCode with mask' => [
        '01001-000',
        true,
    ],
    'invalid ZipCode' => [
        'some random string',
        false,
    ],
]);
