<?php

namespace ValeSaude\PaymentGatewayClient\Models\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 */
trait HasDefaultAttributesOnInitializeTrait
{
    /**
     * @return array<string, mixed>
     */
    public function getDefaultAttributes(): array
    {
        return [];
    }

    public function initializeHasDefaultAttributesOnInitializeTrait(): void
    {
        foreach ($this->getDefaultAttributes() as $attribute => $value) {
            $this->setAttribute($attribute, $value);
        }
    }
}
