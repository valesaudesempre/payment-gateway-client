<?php

namespace ValeSaude\PaymentGatewayClient\QueryBuilders;

use ValeSaude\PaymentGatewayClient\Invoice\Enums\InvoicePaymentMethod;
use ValeSaude\PaymentGatewayClient\Invoice\Enums\InvoiceStatus;
use ValeSaude\PaymentGatewayClient\Models\Invoice;

/**
 * @method Invoice|null first(string|string[] $columns = ['*'])
 * @method Invoice firstOrFail(string|string[] $columns = ['*'])
 */
class InvoiceQueryBuilder extends AbstractGatewayModelQueryBuilder
{
    public function whereStatus(InvoiceStatus ...$status): self
    {
        return $this->whereIn(
            $this->qualifyColumn('status'),
            $status
        );
    }

    public function withPaymentMethod(InvoicePaymentMethod ...$method): self
    {
        return $this->whereIn(
            $this->qualifyColumn('available_payment_methods'),
            $method
        );
    }
}
