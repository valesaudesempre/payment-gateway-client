<?php

namespace ValeSaude\PaymentGatewayClient\Gateways\Iugu\Webhook;

use ValeSaude\PaymentGatewayClient\Events\InvoiceCanceledViaWebhook;
use ValeSaude\PaymentGatewayClient\Events\InvoicePaidViaWebhook;
use ValeSaude\PaymentGatewayClient\Events\InvoiceRefundedViaWebhook;
use ValeSaude\PaymentGatewayClient\Gateways\Contracts\WebhookEventHandlerInterface;
use ValeSaude\PaymentGatewayClient\Gateways\Exceptions\UnexpectedWebhookPayloadException;
use ValeSaude\PaymentGatewayClient\Gateways\Exceptions\WebhookSubjectNotFound;
use ValeSaude\PaymentGatewayClient\Gateways\Iugu\IuguGateway;
use ValeSaude\PaymentGatewayClient\Gateways\Iugu\Webhook\Enums\InvoiceEvent;
use ValeSaude\PaymentGatewayClient\Gateways\Utils\AttributeConverter;
use ValeSaude\PaymentGatewayClient\Invoice\Enums\InvoiceStatus;
use ValeSaude\PaymentGatewayClient\Models\Invoice;
use ValeSaude\PaymentGatewayClient\Models\Webhook;

class IuguInvoiceEventHandler implements WebhookEventHandlerInterface
{
    private IuguGateway $gateway;

    public function __construct(IuguGateway $gateway)
    {
        $this->gateway = $gateway;
    }

    public function shouldHandle(Webhook $webhook): bool
    {
        return in_array($webhook->event, [
            InvoiceEvent::CREATED(),
            InvoiceEvent::STATUS_CHANGED(),
            InvoiceEvent::REFUND(),
            InvoiceEvent::PAYMENT_FAILED(),
            InvoiceEvent::DUE(),
        ]);
    }

    public function handle(Webhook $webhook): void
    {
        $invoice = $this->resolveInvoice($webhook);

        if (!$invoice) {
            return;
        }

        /** @var string|null $status */
        $status = $webhook->request->get('data.status');

        if (!$status) {
            throw UnexpectedWebhookPayloadException
                ::withWebhookAndReason($webhook, 'Missing required data.status property.');
        }

        $newInvoiceStatus = AttributeConverter::convertIuguStatusToInvoiceStatus($status);

        if ($invoice->status->equals($newInvoiceStatus)) {
            // Como precisamos manter tratar os eventos com "redundância", devemos ignorar eventos que alterem
            // o status para o status atual da fatura
            return;
        }

        if (!$invoice->status->canTransitionTo($newInvoiceStatus)) {
            // TODO: Validar se devemos tratar esse tipo de "erro"
            return;
        }

        $previousInvoiceStatus = $invoice->status;

        if ($newInvoiceStatus->equals(InvoiceStatus::PAID())) {
            $invoice->markAsPaid();

            event(new InvoicePaidViaWebhook($webhook, $invoice, $previousInvoiceStatus));
        } elseif ($newInvoiceStatus->equals(InvoiceStatus::CANCELED())) {
            $invoice->markAsCanceled();

            event(new InvoiceCanceledViaWebhook($webhook, $invoice, $previousInvoiceStatus));
        } elseif ($newInvoiceStatus->equals(InvoiceStatus::REFUNDED())) {
            // TODO: Utilizar valor reembolsado constante no gateway
            $invoice->markAsRefunded($invoice->total);

            event(new InvoiceRefundedViaWebhook($webhook, $invoice, $previousInvoiceStatus));
        } else {
            $invoice->setStatus($newInvoiceStatus);
        }
    }

    private function resolveInvoice(Webhook $webhook): ?Invoice
    {
        /** @var string|null $subjectId */
        $subjectId = $webhook->request->get('data.id');

        if (!$subjectId) {
            throw UnexpectedWebhookPayloadException
                ::withWebhookAndReason($webhook, 'Missing required data.id property.');
        }

        $invoice = Invoice::findUsingGatewayId($this->gateway->getGatewayIdentifier(), $subjectId);

        if (!$invoice) {
            if ((string) InvoiceEvent::CREATED() === $webhook->event) {
                // TODO: No futuro, considerar se será necessário cadastrar a fatura criada no lado do gateway
                // Faturas geradas por assinatura são geradas diretamente lá, então não as teríamos aqui
                return null;
            }

            throw WebhookSubjectNotFound
                ::withWebhookAndReason($webhook, 'No invoice found with gateway id.');
        }

        return $invoice;
    }
}
