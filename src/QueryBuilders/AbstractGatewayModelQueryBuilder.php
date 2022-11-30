<?php

namespace ValeSaude\PaymentGatewayClient\QueryBuilders;

use Illuminate\Database\Eloquent\Builder;

class AbstractGatewayModelQueryBuilder extends Builder
{
    /**
     * @return static
     */
    public function belongsToGateway(string $gatewaySlug): self
    {
        return $this->where(
            $this->qualifyColumn('gateway_slug'),
            $gatewaySlug
        );
    }
}
