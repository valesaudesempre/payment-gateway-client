<?php

namespace ValeSaude\PaymentGatewayClient\Invoice\Collections;

use ValeSaude\PaymentGatewayClient\Collections\AbstractCollection;
use ValeSaude\PaymentGatewayClient\Invoice\GatewayInvoiceItemDTO;

/**
 * @extends AbstractCollection<GatewayInvoiceItemDTO>
 */
class GatewayInvoiceItemDTOCollection extends AbstractCollection
{
    public function getSubjectClass(): string
    {
        return GatewayInvoiceItemDTO::class;
    }
}
