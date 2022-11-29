<?php

namespace ValeSaude\PaymentGatewayClient\Invoice;

use Carbon\Carbon;
use ValeSaude\PaymentGatewayClient\Collections\InvoiceSplitRuleCollection;
use ValeSaude\PaymentGatewayClient\Invoice\Collections\InvoiceItemDTOCollection;
use ValeSaude\PaymentGatewayClient\Invoice\Collections\InvoicePaymentMethodCollection;

class InvoiceDTO
{
    public Carbon $dueDate;
    public InvoiceItemDTOCollection $items;
    public int $maxInstallments = 1;
    public ?InvoicePaymentMethodCollection $availablePaymentMethods;
    public ?InvoiceSplitRuleCollection $splits;

    public function __construct(
        Carbon $dueDate,
        InvoiceItemDTOCollection $items,
        int $maxInstallments,
        ?InvoicePaymentMethodCollection $availablePaymentMethods = null,
        ?InvoiceSplitRuleCollection $splits = null
    ) {
        $this->dueDate = $dueDate;
        $this->items = $items;
        $this->maxInstallments = $maxInstallments;
        $this->availablePaymentMethods = $availablePaymentMethods;
        $this->splits = $splits;
    }
}
