<?php

namespace ValeSaude\PaymentGatewayClient\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string|null $owner_id
 * @property string|null $owner_type
 * @property Model       $owner
 *
 * @mixin Model
 */
trait HasOwnerTrait
{
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }
}
