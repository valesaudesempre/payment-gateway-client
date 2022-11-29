<?php

namespace ValeSaude\PaymentGatewayClient\Invoice;

use ValeSaude\PaymentGatewayClient\ValueObjects\Money;

class GatewayInvoiceItemDTO
{
    public ?string $id;
    public Money $price;
    public int $quantity;
    public string $description;

    public function __construct(?string $id, Money $price, int $quantity, string $description)
    {
        $this->id = $id;
        $this->price = $price;
        $this->quantity = $quantity;
        $this->description = $description;
    }

    public static function fromInvoiceItemDTO(InvoiceItemDTO $data): self
    {
        return new self(null, $data->price, $data->quantity, $data->description);
    }
}
