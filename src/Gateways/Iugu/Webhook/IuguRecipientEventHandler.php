<?php

namespace ValeSaude\PaymentGatewayClient\Gateways\Iugu\Webhook;

use ValeSaude\PaymentGatewayClient\Events\RecipientApprovedViaWebhook;
use ValeSaude\PaymentGatewayClient\Events\RecipientDeclinedViaWebhook;
use ValeSaude\PaymentGatewayClient\Gateways\Contracts\WebhookEventHandlerInterface;
use ValeSaude\PaymentGatewayClient\Gateways\Exceptions\UnexpectedWebhookPayloadException;
use ValeSaude\PaymentGatewayClient\Gateways\Exceptions\WebhookSubjectNotFound;
use ValeSaude\PaymentGatewayClient\Gateways\Iugu\IuguGateway;
use ValeSaude\PaymentGatewayClient\Gateways\Iugu\Webhook\Enums\IuguRecipientEvent;
use ValeSaude\PaymentGatewayClient\Models\Recipient;
use ValeSaude\PaymentGatewayClient\Models\Webhook;

class IuguRecipientEventHandler implements WebhookEventHandlerInterface
{
    private IuguGateway $gateway;

    public function __construct(IuguGateway $gateway)
    {
        $this->gateway = $gateway;
    }

    public function shouldHandle(Webhook $webhook): bool
    {
        return $webhook->event === (string) IuguRecipientEvent::REFERRALS_VERIFICATION();
    }

    public function handle(Webhook $webhook): void
    {
        /** @var string|null $subjectId */
        $subjectId = $webhook->request->get('data.account_id');

        if (!$subjectId) {
            throw UnexpectedWebhookPayloadException
                ::withWebhookAndReason($webhook, 'Missing required data.account_id property.');
        }

        $recipient = Recipient::findUsingGatewayId($this->gateway->getGatewayIdentifier(), $subjectId);

        if (!$recipient) {
            throw WebhookSubjectNotFound
                ::withWebhookAndReason($webhook, 'No recipient found with gateway id.');
        }

        /** @var string|null $status */
        $status = $webhook->request->get('data.status');

        if (!$status) {
            throw UnexpectedWebhookPayloadException
                ::withWebhookAndReason($webhook, 'Missing required data.status property.');
        }

        if ('accepted' === $status) {
            $recipient->markAsApproved();
            event(new RecipientApprovedViaWebhook($webhook, $recipient));
        } else {
            $recipient->markAsDeclined();
            event(new RecipientDeclinedViaWebhook($webhook, $recipient));
        }
    }
}
