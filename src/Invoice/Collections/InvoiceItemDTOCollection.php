<?php

namespace ValeSaude\PaymentGatewayClient\Invoice\Collections;

use ValeSaude\PaymentGatewayClient\Collections\AbstractCollection;
use ValeSaude\PaymentGatewayClient\Invoice\InvoiceItemDTO;

/**
 * @extends AbstractCollection<InvoiceItemDTO>
 */
class InvoiceItemDTOCollection extends AbstractCollection
{
    public function getSubjectClass(): string
    {
        return InvoiceItemDTO::class;
    }
}
