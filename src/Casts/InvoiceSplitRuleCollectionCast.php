<?php

namespace ValeSaude\PaymentGatewayClient\Casts;

use ValeSaude\LaravelValueObjects\Money;
use ValeSaude\PaymentGatewayClient\Collections\InvoiceSplitRuleCollection;
use ValeSaude\PaymentGatewayClient\ValueObjects\InvoiceSplitRule;

/**
 * @extends AbstractCollectionCast<InvoiceSplitRule, InvoiceSplitRuleCollection>
 */
class InvoiceSplitRuleCollectionCast extends AbstractCollectionCast
{
    public function getCollectionClass(): string
    {
        return InvoiceSplitRuleCollection::class;
    }

    /**
     * @param array{recipient_id: string, amount: int} $item
     */
    public function castToSubject($item): InvoiceSplitRule
    {
        return new InvoiceSplitRule($item['recipient_id'], new Money($item['amount']));
    }
}
