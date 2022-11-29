<?php

namespace ValeSaude\PaymentGatewayClient\Gateways\Iugu;

use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use ValeSaude\PaymentGatewayClient\Customer\CustomerDTO;
use ValeSaude\PaymentGatewayClient\Gateways\Contracts\GatewayInterface;

class IuguGateway implements GatewayInterface
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
    public function createCustomer(CustomerDTO $data, string $internalId): string
    {
        $response = $this
            ->doRequest(
                'POST',
                'v1/customers',
                [
                    'email' => (string) $data->email,
                    'name' => $data->name,
                    'cpf_cnpj' => (string) $data->documentNumber,
                    'zip_code' => (string) $data->address->getZipCode(),
                    'number' => $data->address->getNumber(),
                    'street' => $data->address->getStreet(),
                    'city' => $data->address->getCity(),
                    'state' => $data->address->getState(),
                    'district' => $data->address->getDistrict(),
                    'complement' => $data->address->getComplement(),
                    'custom_variables' => [
                        [
                            'name' => 'external_reference',
                            'value' => $internalId,
                        ],
                    ],
                ]
            );

        return $response->json('id');
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
