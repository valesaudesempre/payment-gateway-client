<?php

namespace ValeSaude\PaymentGatewayClient\Webhook;

use Spatie\Enum\Laravel\Enum;
use ValeSaude\PaymentGatewayClient\Concerns\ConvertsEnumValueToSlugTrait;

/**
 * @method static self PENDING()
 * @method static self SUCCESS()
 * @method static self ERROR()
 * @method static self IGNORED()
 */
final class WebhookStatus extends Enum
{
    use ConvertsEnumValueToSlugTrait;
}
