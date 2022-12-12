<?php

namespace ValeSaude\PaymentGatewayClient\Recipient\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self PENDING()
 * @method static self APPROVED()
 * @method static self DECLINED()
 */
final class RecipientStatus extends Enum
{
}
