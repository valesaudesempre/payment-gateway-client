<?php

namespace ValeSaude\PaymentGatewayClient\Validators;

use ValeSaude\PaymentGatewayClient\Validators\Contracts\ValidatorInterface;

class CPFValidator implements ValidatorInterface
{
    public function sanitize(string $value): string
    {
        return preg_replace('/\D/', '', $value) ?? '';
    }

    /**
     * @see https://gist.github.com/rafael-neri/ab3e58803a08cb4def059fce4e3c0e40
     */
    public function validate(string $value): bool
    {
        $cpf = $this->sanitize($value);

        if (strlen($cpf) !== 11) {
            return false;
        }

        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += ((int) $cpf[$c]) * (($t + 1) - $c);
            }

            $d = ((10 * $d) % 11) % 10;

            if ($cpf[$c] != $d) {
                return false;
            }
        }

        return true;
    }
}
