<?php

namespace ValeSaude\PaymentGatewayClient\Http\Controllers;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use ValeSaude\PaymentGatewayClient\Gateways\GatewayManager;
use ValeSaude\PaymentGatewayClient\Jobs\ProcessGatewayWebhookJob;

class WebhookController extends Controller
{
    public function __invoke(Request $request, string $gateway): Response
    {
        try {
            $processor = GatewayManager::resolveWebhookProcessor($gateway);
        } catch (BindingResolutionException $e) {
            return response('Gateway settings not found.', 404);
        }

        if (!$processor->authenticate($request)) {
            return response('Authentication failed.', 401);
        }

        // @phpstan-ignore-next-line
        dispatch(new ProcessGatewayWebhookJob($gateway, $request->input(), $request->header(), CarbonImmutable::now()));

        // @phpstan-ignore-next-line
        return response();
    }
}
