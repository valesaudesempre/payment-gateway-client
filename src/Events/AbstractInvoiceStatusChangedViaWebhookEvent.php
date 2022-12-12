<?php

namespace ValeSaude\PaymentGatewayClient\Events;

use ValeSaude\PaymentGatewayClient\Invoice\Enums\InvoiceStatus;
use ValeSaude\PaymentGatewayClient\Models\Invoice;
use ValeSaude\PaymentGatewayClient\Models\Webhook;

abstract class AbstractInvoiceStatusChangedViaWebhookEvent extends AbstractWebhookEvent
{
    private Invoice $invoice;
    private InvoiceStatus $previousStatus;

    public function __construct(Webhook $webhook, Invoice $recipient, InvoiceStatus $previousStatus)
    {
        parent::__construct($webhook);
        $this->invoice = $recipient;
        $this->previousStatus = $previousStatus;
    }

    public function getSubject(): Invoice
    {
        return $this->invoice;
    }

    public function getPreviousStatus(): InvoiceStatus
    {
        return $this->previousStatus;
    }
}
