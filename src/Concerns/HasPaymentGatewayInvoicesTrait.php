<?php

namespace ValeSaude\PaymentGatewayClient\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use ValeSaude\PaymentGatewayClient\Invoice\Enums\InvoicePaymentMethod;
use ValeSaude\PaymentGatewayClient\Invoice\Enums\InvoiceStatus;
use ValeSaude\PaymentGatewayClient\Models\Invoice;
use ValeSaude\PaymentGatewayClient\QueryBuilders\InvoiceQueryBuilder;

/**
 * @mixin Model
 */
trait HasPaymentGatewayInvoicesTrait
{
    public function getDefaultGatewaySlug(): string
    {
        return config('payment-gateway-client.default_gateway');
    }

    /**
     * @return MorphMany|InvoiceQueryBuilder
     */
    public function gatewayInvoices(): MorphMany
    {
        return $this->morphMany(Invoice::class, 'owner');
    }

    public function getLatestPendingInvoice(?InvoicePaymentMethod $method = null, ?string $gatewaySlug = null): ?Invoice
    {
        $query = $this
            ->gatewayInvoices()
            ->whereStatus(InvoiceStatus::PENDING())
            ->belongsToGateway($gatewaySlug ?? $this->getDefaultGatewaySlug())
            ->latest();

        if ($method) {
            $query->withPaymentMethod($method);
        }

        return $query->first();
    }
}

