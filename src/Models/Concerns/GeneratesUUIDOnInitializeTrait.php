<?php

namespace ValeSaude\PaymentGatewayClient\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @mixin Model
 *
 * @property string $id
 */
trait GeneratesUUIDOnInitializeTrait
{
    public function initializeGeneratesUUIDOnInitializeTrait(): void
    {
        $this->id = (string) Str::uuid();
    }
}
