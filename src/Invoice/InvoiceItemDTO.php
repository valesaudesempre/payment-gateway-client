<?php

namespace ValeSaude\PaymentGatewayClient\Invoice;

use ValeSaude\PaymentGatewayClient\ValueObjects\Money;

class InvoiceItemDTO
{
    public Money $price;
    public int $quantity;
    public string $description;

    public function __construct(Money $price, int $quantity, string $description)
    {
        $this->price = $price;
        $this->quantity = $quantity;
        $this->description = $description;
    }

    public static function fromGatewayInvoiceItemDTO(GatewayInvoiceItemDTO $data): self
    {
        return new self($data->price, $data->quantity, $data->description);
    }
}
