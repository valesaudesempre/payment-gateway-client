<?php

namespace ValeSaude\PaymentGatewayClient\Invoice;

use Carbon\CarbonInterface;
use ValeSaude\LaravelValueObjects\Money;
use ValeSaude\PaymentGatewayClient\Invoice\Collections\GatewayInvoiceItemDTOCollection;
use ValeSaude\PaymentGatewayClient\Invoice\Enums\InvoiceStatus;

class GatewayInvoiceDTO
{
    public string $id;
    public ?string $url;
    public CarbonInterface $dueDate;
    public InvoiceStatus $status;
    public GatewayInvoiceItemDTOCollection $items;
    public ?int $installments;
    public ?string $bankSlipCode;
    public ?string $pixCode;
    public ?CarbonInterface $paidAt;
    public ?CarbonInterface $refundedAt;
    public ?Money $refundedAmount;

    public function __construct(
        string $id,
        ?string $url,
        CarbonInterface $dueDate,
        InvoiceStatus $status,
        GatewayInvoiceItemDTOCollection $items,
        ?int $installments = null,
        ?string $bankSlipCode = null,
        ?string $pixCode = null,
        ?CarbonInterface $paidAt = null,
        ?CarbonInterface $refundedAt = null,
        ?Money $refundedAmount = null
    ) {
        $this->id = $id;
        $this->url = $url;
        $this->dueDate = $dueDate;
        $this->status = $status;
        $this->items = $items;
        $this->installments = $installments;
        $this->bankSlipCode = $bankSlipCode;
        $this->pixCode = $pixCode;
        $this->paidAt = $paidAt;
        $this->refundedAt = $refundedAt;
        $this->refundedAmount = $refundedAmount;
    }
}
