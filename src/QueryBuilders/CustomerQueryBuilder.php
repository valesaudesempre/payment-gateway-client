<?php

namespace ValeSaude\PaymentGatewayClient\QueryBuilders;

use ValeSaude\PaymentGatewayClient\Models\Customer;

/**
 * @method Customer|null first(string|string[] $columns = ['*'])
 * @method Customer firstOrFail(string|string[] $columns = ['*'])
 */
class CustomerQueryBuilder extends AbstractGatewayModelQueryBuilder
{
}
