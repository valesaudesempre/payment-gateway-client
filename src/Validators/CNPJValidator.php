<?php

namespace ValeSaude\PaymentGatewayClient\Validators;

use ValeSaude\PaymentGatewayClient\Validators\Contracts\ValidatorInterface;

class CNPJValidator implements ValidatorInterface
{
    public function sanitize(string $value): string
    {
        return preg_replace('/\D/', '', $value) ?? '';
    }

    /**
     * @see https://gist.github.com/guisehn/3276302?permalink_comment_id=3365976#gistcomment-3365976
     */
    public function validate(string $value): bool
    {
        $cnpj = $this->sanitize($value);

        if (strlen($cnpj) !== 14) {
            return false;
        }

        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        for ($t = 12; $t < 14; $t++) {
            for ($d = 0, $m = ($t - 7), $i = 0; $i < $t; $i++) {
                $d += ((int) $cnpj[$i]) * $m;
                $m = ($m == 2 ? 9 : --$m);
            }

            $d = ((10 * $d) % 11) % 10;

            if ($cnpj[$i] != $d) {
                return false;
            }
        }

        return true;
    }
}
