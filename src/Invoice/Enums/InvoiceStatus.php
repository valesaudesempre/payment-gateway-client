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

    public function canTransitionTo(self $status): bool
    {
        foreach ($this->getAllowedTransitionsStatus() as $allowedStatus) {
            if ($allowedStatus->equals($status)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return self[]
     */
    public function getAllowedTransitionsStatus(): array
    {
        if ($this->equals(self::PENDING())) {
            return [
                self::PAID(),
                self::CANCELED(),
                self::EXPIRED(),
                self::AUTHORIZED(),
            ];
        }

        if ($this->equals(self::PAID())) {
            return [self::REFUNDED()];
        }

        if ($this->equals(self::AUTHORIZED())) {
            return [self::PAID()];
        }

        return [];
    }
}
