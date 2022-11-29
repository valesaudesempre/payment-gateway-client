<?php

namespace ValeSaude\PaymentGatewayClient\Casts;

use ValeSaude\PaymentGatewayClient\Invoice\Collections\InvoicePaymentMethodCollection;
use ValeSaude\PaymentGatewayClient\Invoice\Enums\InvoicePaymentMethod;

/**
 * @extends AbstractCollectionCast<InvoicePaymentMethod, InvoicePaymentMethodCollection>
 */
class InvoicePaymentMethodCollectionCast extends AbstractCollectionCast
{
    public function getCollectionClass(): string
    {
        return InvoicePaymentMethodCollection::class;
    }

    /**
     * @param string $item
     */
    public function castToSubject($item)
    {
        return InvoicePaymentMethod::from($item);
    }
}
