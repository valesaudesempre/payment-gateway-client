<?php

namespace ValeSaude\PaymentGatewayClient\Collections;

use ValeSaude\PaymentGatewayClient\ValueObjects\InvoiceSplitRule;

/**
 * @extends AbstractCollection<InvoiceSplitRule>
 */
class InvoiceSplitRuleCollection extends AbstractCollection
{
    public function getSubjectClass(): string
    {
        return InvoiceSplitRule::class;
    }
}
