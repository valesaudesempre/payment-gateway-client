<?php

namespace ValeSaude\PaymentGatewayClient\Customer;

class PaymentMethodDTO
{
    public string $description;
    public string $token;

    public function __construct(string $description, string $token)
    {
        $this->description = $description;
        $this->token = $token;
    }
}
