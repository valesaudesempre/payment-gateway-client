<?php

namespace ValeSaude\PaymentGatewayClient\Customer;

use ValeSaude\PaymentGatewayClient\ValueObjects\CreditCard;

class GatewayPaymentMethodDTO
{
    public string $id;
    public CreditCard $card;

    public function __construct(
        string $id,
        CreditCard $card
    ) {
        $this->id = $id;
        $this->card = $card;
    }
}
