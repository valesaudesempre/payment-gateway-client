<?php

namespace ValeSaude\PaymentGatewayClient\Gateways\Iugu;

use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use ValeSaude\PaymentGatewayClient\Customer\CustomerDTO;
use ValeSaude\PaymentGatewayClient\Gateways\AbstractGateway;
use ValeSaude\PaymentGatewayClient\Gateways\Iugu\Builders\IuguCustomerBuilder;

class IuguGateway extends AbstractGateway
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct(string $baseUrl, string $apiKey)
    {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;
    }

    /**
     * @throws RequestException
     */
    public function createCustomer(CustomerDTO $data, string $externalReference): string
    {
        $response = $this
            ->doRequest(
                'POST',
                'v1/customers',
                IuguCustomerBuilder::make()
                    ->fromCustomerDTO($data)
                    ->setExternalReference($externalReference)
                    ->get()
            );

        return $response->json('id');
    }

    public function updateCustomer(string $id, CustomerDTO $data): void
    {
        $this->doRequest(
            'PUT',
            "v1/customers/{$id}",
            IuguCustomerBuilder::make()
                ->fromCustomerDTO($data)
                ->get()
        );
    }

    public function getGatewayIdentifier(): string
    {
        return 'iugu';
    }

    /**
     * @param array<array-key, mixed> $data
     *
     * @throws RequestException
     */
    protected function doRequest(string $method, string $uri, array $data = [], bool $throwOnError = true): Response
    {
        $pendingRequest = Http
            ::asJson()
            ->baseUrl($this->baseUrl)
            ->withToken(base64_encode($this->apiKey), 'Basic');

        if (!\in_array($method, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'])) {
            throw new InvalidArgumentException("Unsupported HTTP method {$method}.");
        }

        /** @var Response $response */
        $response = $pendingRequest->{strtolower($method)}($uri, $data);

        if ($throwOnError) {
            $response->throw();
        }

        return $response;
    }
}
