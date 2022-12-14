<?php

namespace ValeSaude\PaymentGatewayClient\Tests\Concerns;

use ValeSaude\PaymentGatewayClient\Models\Recipient;
use ValeSaude\PaymentGatewayClient\Recipient\RecipientDTO;
use ValeSaude\PaymentGatewayClient\Tests\TestCase;

/**
 * @mixin TestCase
 */
trait HasRecipientHelperMethodsTrait
{
    public function createRecipientDTO(): RecipientDTO
    {
        return Recipient::factory()
            ->make()
            ->toRecipientDTO();
    }
}
