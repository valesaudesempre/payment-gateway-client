<?php

namespace ValeSaude\PaymentGatewayClient\Concerns;

use Illuminate\Support\Str;
use Spatie\Enum\Laravel\Enum;

/**
 * @mixin Enum
 */
trait ConvertsEnumValueToSlugTrait
{
    /**
     * @return callable(string): string
     */
    protected static function values(): callable
    {
        return static fn (string $value) => Str::slug($value);
    }
}
