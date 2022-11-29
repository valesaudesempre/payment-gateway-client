<?php

namespace ValeSaude\PaymentGatewayClient\Gateways\Iugu\Webhook\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self CREATED()
 * @method static self STATUS_CHANGED()
 * @method static self REFUND()
 * @method static self PAYMENT_FAILED()
 * @method static self DUE()
 */
final class InvoiceEvent extends Enum
{
    /**
     * @return array<string, string>
     */
    protected static function values(): array
    {
        return [
            'CREATED' => 'invoice.created',
            'STATUS_CHANGED' => 'invoice.status_changed',
            'REFUND' => 'invoice.refund',
            'PAYMENT_FAILED' => 'invoice.payment_failed',
            'DUE' => 'invoice.due',
        ];
    }
}
