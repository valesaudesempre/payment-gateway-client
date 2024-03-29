<?php

namespace ValeSaude\PaymentGatewayClient\Gateways\Iugu\Webhook;

use ValeSaude\PaymentGatewayClient\Events\InvoiceCanceledViaWebhook;
use ValeSaude\PaymentGatewayClient\Events\InvoicePaidViaWebhook;
use ValeSaude\PaymentGatewayClient\Events\InvoiceRefundedViaWebhook;
use ValeSaude\PaymentGatewayClient\Gateways\Contracts\WebhookEventHandlerInterface;
use ValeSaude\PaymentGatewayClient\Gateways\Exceptions\UnexpectedWebhookPayloadException;
use ValeSaude\PaymentGatewayClient\Gateways\Exceptions\WebhookSubjectNotFound;
use ValeSaude\PaymentGatewayClient\Gateways\Iugu\IuguGateway;
use ValeSaude\PaymentGatewayClient\Gateways\Iugu\Utils\IuguAttributeConverter;
use ValeSaude\PaymentGatewayClient\Gateways\Iugu\Webhook\Enums\IuguInvoiceEvent;
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
            IuguInvoiceEvent::CREATED(),
            IuguInvoiceEvent::STATUS_CHANGED(),
            IuguInvoiceEvent::REFUND(),
            IuguInvoiceEvent::PAYMENT_FAILED(),
            IuguInvoiceEvent::DUE(),
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

        $newInvoiceStatus = IuguAttributeConverter::convertIuguStatusToInvoiceStatus($status);

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
            $this->handlePaidInvoice($invoice, $webhook, $previousInvoiceStatus);

            return;
        }

        if ($newInvoiceStatus->equals(InvoiceStatus::CANCELED())) {
            $this->handleCanceledInvoice($invoice, $webhook, $previousInvoiceStatus);

            return;
        }

        if ($newInvoiceStatus->equals(InvoiceStatus::REFUNDED())) {
            $this->handleRefundedInvoice($invoice, $webhook, $previousInvoiceStatus);

            return;
        }

        $invoice->setStatus($newInvoiceStatus);
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
            if ((string) IuguInvoiceEvent::CREATED() === $webhook->event) {
                // TODO: No futuro, considerar se será necessário cadastrar a fatura criada no lado do gateway
                // Faturas geradas por assinatura são geradas diretamente lá, então não as teríamos aqui
                return null;
            }

            throw WebhookSubjectNotFound
                ::withWebhookAndReason($webhook, 'No invoice found with gateway id.');
        }

        return $invoice;
    }

    private function handlePaidInvoice(Invoice $invoice, Webhook $webhook, InvoiceStatus $previousInvoiceStatus): void
    {
        // @phpstan-ignore-next-line
        $gatewayInvoice = $this->gateway->getInvoice($invoice->gateway_id);

        $invoice->installments = $gatewayInvoice->installments;
        // @phpstan-ignore-next-line
        $invoice->markAsPaid($gatewayInvoice->paidAt->toImmutable());

        event(new InvoicePaidViaWebhook($webhook, $invoice, $previousInvoiceStatus));
    }

    private function handleCanceledInvoice(Invoice $invoice, Webhook $webhook, InvoiceStatus $previousInvoiceStatus): void
    {
        $invoice->markAsCanceled();

        event(new InvoiceCanceledViaWebhook($webhook, $invoice, $previousInvoiceStatus));
    }

    private function handleRefundedInvoice(Invoice $invoice, Webhook $webhook, InvoiceStatus $previousInvoiceStatus): void
    {
        // TODO: Utilizar valor reembolsado constante no gateway
        $invoice->markAsRefunded($invoice->total);

        event(new InvoiceRefundedViaWebhook($webhook, $invoice, $previousInvoiceStatus));
    }
}
