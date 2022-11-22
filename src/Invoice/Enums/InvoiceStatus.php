<?php

namespace ValeSaude\PaymentGatewayClient\Invoice\Enums;

use Spatie\Enum\Laravel\Enum;
use ValeSaude\PaymentGatewayClient\Concerns\ConvertsEnumValueToSlugTrait;

/**
 * @method static self PENDING()
 * @method static self PAID()
 * @method static self CANCELED()
 * @method static self REFUNDED()
 * @method static self EXPIRED()
 * @method static self AUTHORIZED()
 */
final class InvoiceStatus extends Enum
{
    use ConvertsEnumValueToSlugTrait;
}
