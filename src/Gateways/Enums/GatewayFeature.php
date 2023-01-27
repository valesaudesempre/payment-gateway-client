<?php

namespace ValeSaude\PaymentGatewayClient\Gateways\Enums;

use Spatie\Enum\Enum;

/**
 * @method static self CUSTOMER()
 * @method static self PAYMENT_METHOD()
 * @method static self INVOICE()
 * @method static self INVOICE_SPLIT()
 * @method static self INVOICE_PARTIAL_REFUND()
 * @method static self RECIPIENT()
 */
final class GatewayFeature extends Enum
{
}
