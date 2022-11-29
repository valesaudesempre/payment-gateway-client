<?php

namespace ValeSaude\PaymentGatewayClient\Gateways\Iugu\Webhook;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use UnexpectedValueException;
use ValeSaude\PaymentGatewayClient\Gateways\Contracts\WebhookEventHandlerInterface;
use ValeSaude\PaymentGatewayClient\Gateways\Contracts\WebhookProcessorInterface;
use ValeSaude\PaymentGatewayClient\Models\Webhook;

class IuguWebhookProcessor implements WebhookProcessorInterface
{
    public function authenticate(Request $request): bool
    {
        /** @var string $token */
        $token = $request->header('Authorization', '');

        return Hash::check($token, config('services.iugu.webhook_token'));
    }

    public function process(Webhook $webhook): bool
    {
        $request = $webhook->request;
        $event = $request->get('event');

        if (!isset($event)) {
            throw new UnexpectedValueException('The webhook event name is invalid.');
        }

        $webhook->event = $event;

        foreach ($this->getEventHandlers() as $handlerClass) {
            /** @var WebhookEventHandlerInterface $handler */
            $handler = resolve($handlerClass);

            if ($handler->shouldHandle($webhook)) {
                $handler->handle($webhook);

                return true;
            }
        }

        return false;
    }

    public function getEventHandlers(): array
    {
        return [
            IuguInvoiceEventHandler::class,
        ];
    }
}
