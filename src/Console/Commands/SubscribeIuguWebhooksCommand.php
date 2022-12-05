<?php

namespace ValeSaude\PaymentGatewayClient\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use ValeSaude\PaymentGatewayClient\Gateways\Iugu\IuguGateway;

class SubscribeIuguWebhooksCommand extends Command
{
    protected $signature = 'gateway:subscribe-iugu-webhooks';

    protected $description = 'Cadastra a URL de webhook da aplicação paga o gateway Iugu.';

    public function handle(IuguGateway $gateway): int
    {
        $token = Hash::make(config('services.iugu.webhook_token'));

        $gateway->subscribeWebhook($token);

        return 0;
    }
}
