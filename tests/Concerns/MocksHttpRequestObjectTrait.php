<?php

namespace ValeSaude\PaymentGatewayClient\Tests\Concerns;

use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

/**
 * @mixin TestCase
 */
trait MocksHttpRequestObjectTrait
{
    public function createFakeRequestObject(
        array $parameters = [],
        array $headers = [],
        string $path = '/',
        string $method = 'GET'
    ): SymfonyRequest {
        $symfonyRequest = SymfonyRequest::create(
            $path,
            $method,
            $parameters,
            [],
            [],
            $this->transformHeadersToServerVars($headers)
        );

        return Request::createFromBase($symfonyRequest);
    }
}
