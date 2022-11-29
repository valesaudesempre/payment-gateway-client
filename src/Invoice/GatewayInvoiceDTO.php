<?php

namespace ValeSaude\PaymentGatewayClient\Invoice;

use Carbon\CarbonInterface;
use ValeSaude\PaymentGatewayClient\Invoice\Collections\GatewayInvoiceItemDTOCollection;
use ValeSaude\PaymentGatewayClient\Invoice\Enums\InvoiceStatus;

class GatewayInvoiceDTO
{
    public string $id;
    public ?string $url;
    public CarbonInterface $dueDate;
    public InvoiceStatus $status;
    public GatewayInvoiceItemDTOCollection $items;
    public ?string $bankSlipCode;
    public ?string $pixCode;

    public function __construct(
        string $id,
        string $url,
        CarbonInterface $dueDate,
        InvoiceStatus $status,
        GatewayInvoiceItemDTOCollection $items,
        ?string $bankSlipCode = null,
        ?string $pixCode = null
    ) {
        $this->id = $id;
        $this->url = $url;
        $this->dueDate = $dueDate;
        $this->status = $status;
        $this->items = $items;
        $this->bankSlipCode = $bankSlipCode;
        $this->pixCode = $pixCode;
    }

}
