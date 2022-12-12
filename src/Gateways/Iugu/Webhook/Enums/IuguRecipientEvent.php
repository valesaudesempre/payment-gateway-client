<?php

namespace ValeSaude\PaymentGatewayClient\Gateways\Iugu\Webhook\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self REFERRALS_VERIFICATION()
 */
final class IuguRecipientEvent extends Enum
{
    /**
     * @return array<string, string>
     */
    protected static function values(): array
    {
        return [
            'REFERRALS_VERIFICATION' => 'referrals.verification',
        ];
    }
}
