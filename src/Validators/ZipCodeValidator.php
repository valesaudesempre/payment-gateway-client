<?php

namespace ValeSaude\PaymentGatewayClient\Validators;

use ValeSaude\PaymentGatewayClient\Validators\Contracts\ValidatorInterface;

class ZipCodeValidator implements ValidatorInterface
{
    public function sanitize(string $value): string
    {
        return preg_replace('/\D/', '', $value) ?? '';
    }

    public function validate(string $value): bool
    {
        return (bool) preg_match('/\d{5}-?\d{3}/', $value);
    }
}
