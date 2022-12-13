<?php

namespace ValeSaude\PaymentGatewayClient\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static Builder withoutDefaultOrdering()
 *
 * @mixin Model
 */
trait HasDefaultOrderingTrait
{
    public function scopeWithoutDefaultOrdering(Builder $query): Builder
    {
        return $query->withoutGlobalScope('latest');
    }

    public static function bootHasDefaultOrderingTrait(): void
    {
        static::addGlobalScope('latest', static function (Builder $query) {
            $query->latest('id');
        });
    }
}
