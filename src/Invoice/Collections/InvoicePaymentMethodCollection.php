<?php

namespace ValeSaude\PaymentGatewayClient\Invoice\Collections;

use ValeSaude\PaymentGatewayClient\Collections\AbstractCollection;
use ValeSaude\PaymentGatewayClient\Invoice\Enums\InvoicePaymentMethod;

/**
 * @extends AbstractCollection<InvoicePaymentMethod>
 */
class InvoicePaymentMethodCollection extends AbstractCollection
{
    public function getSubjectClass(): string
    {
        return InvoicePaymentMethod::class;
    }
}
