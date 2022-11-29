<?php

namespace ValeSaude\PaymentGatewayClient\Invoice\Enums;

use Spatie\Enum\Laravel\Enum;
use ValeSaude\PaymentGatewayClient\Concerns\ConvertsEnumValueToSlugTrait;

/**
 * @method static self BANK_SLIP()
 * @method static self CREDIT_CARD()
 * @method static self PIX()
 */
final class InvoicePaymentMethod extends Enum
{
    use ConvertsEnumValueToSlugTrait;
}
