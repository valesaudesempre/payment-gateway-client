<?php

namespace ValeSaude\PaymentGatewayClient\Concerns;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use ValeSaude\PaymentGatewayClient\Models\Customer;
use ValeSaude\PaymentGatewayClient\Models\PaymentMethod;
use ValeSaude\PaymentGatewayClient\QueryBuilders\CustomerQueryBuilder;

/**
 * @mixin Model
 */
trait HasPaymentGatewayCustomersTrait
{
    public function getDefaultGatewaySlug(): string
    {
        return config('payment-gateway-client.default_gateway');
    }

    /**
     * @return CustomerQueryBuilder|MorphMany
     */
    public function gatewayCustomers(): MorphMany
    {
        return $this->morphMany(Customer::class, 'owner');
    }

    public function getGatewayCustomer(?string $gatewaySlug = null): ?Customer
    {
        return $this
            ->gatewayCustomers()
            ->belongsToGateway($gatewaySlug ?? $this->getDefaultGatewaySlug())
            ->first();
    }

    /**
     * @return Collection<PaymentMethod>|null
     */
    public function getGatewayPaymentMethods(?string $gatewaySlug = null): ?Collection
    {
        $customer = $this->getGatewayCustomer($gatewaySlug);

        if (!$customer) {
            return null;
        }

        return $customer->paymentMethods;
    }
}
