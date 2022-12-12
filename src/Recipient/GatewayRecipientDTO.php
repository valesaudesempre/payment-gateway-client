<?php

namespace ValeSaude\PaymentGatewayClient\Recipient;

use ValeSaude\PaymentGatewayClient\Recipient\Enums\RecipientStatus;
use ValeSaude\PaymentGatewayClient\ValueObjects\JsonObject;

class GatewayRecipientDTO
{
    public string $id;
    public RecipientStatus $status;
    public JsonObject $gatewaySpecificData;

    public function __construct(string $id, RecipientStatus $status, JsonObject $gatewaySpecificData)
    {
        $this->id = $id;
        $this->status = $status;
        $this->gatewaySpecificData = $gatewaySpecificData;
    }
}
