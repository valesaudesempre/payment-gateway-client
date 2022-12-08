<?php

namespace ValeSaude\PaymentGatewayClient\Validators;

use ValeSaude\PaymentGatewayClient\Validators\Contracts\ValidatorInterface;

class PhoneValidator implements ValidatorInterface
{
    public function sanitize(string $value): string
    {
        return preg_replace('/\D/', '', $value) ?? '';
    }

    public function validate(string $value): bool
    {
        $phone = $this->sanitize($value);

        return (bool) preg_match('/^\d{10,11}$/', $phone);
    }
}
