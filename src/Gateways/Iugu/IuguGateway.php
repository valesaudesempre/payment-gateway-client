<?php

namespace ValeSaude\PaymentGatewayClient\Gateways\Iugu;

use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use ValeSaude\LaravelValueObjects\JsonObject;
use ValeSaude\LaravelValueObjects\Money;
use ValeSaude\LaravelValueObjects\Month;
use ValeSaude\LaravelValueObjects\PositiveInteger;
use ValeSaude\PaymentGatewayClient\Customer\CustomerDTO;
use ValeSaude\PaymentGatewayClient\Customer\GatewayPaymentMethodDTO;
use ValeSaude\PaymentGatewayClient\Customer\PaymentMethodDTO;
use ValeSaude\PaymentGatewayClient\Gateways\AbstractGateway;
use ValeSaude\PaymentGatewayClient\Gateways\Enums\GatewayFeature;
use ValeSaude\PaymentGatewayClient\Gateways\Exceptions\InvalidPaymentTokenException;
use ValeSaude\PaymentGatewayClient\Gateways\Exceptions\TransactionDeclinedException;
use ValeSaude\PaymentGatewayClient\Gateways\Iugu\Builders\IuguCustomerBuilder;
use ValeSaude\PaymentGatewayClient\Gateways\Iugu\Builders\IuguInvoiceBuilder;
use ValeSaude\PaymentGatewayClient\Gateways\Iugu\Builders\IuguRecipientBuilder;
use ValeSaude\PaymentGatewayClient\Gateways\Iugu\Exceptions\GenericErrorResponseException;
use ValeSaude\PaymentGatewayClient\Gateways\Iugu\Exceptions\ValidationErrorResponseException;
use ValeSaude\PaymentGatewayClient\Gateways\Iugu\Utils\IuguAttributeConverter;
use ValeSaude\PaymentGatewayClient\Invoice\GatewayInvoiceDTO;
use ValeSaude\PaymentGatewayClient\Invoice\InvoiceDTO;
use ValeSaude\PaymentGatewayClient\Recipient\Enums\RecipientStatus;
use ValeSaude\PaymentGatewayClient\Recipient\GatewayRecipientDTO;
use ValeSaude\PaymentGatewayClient\Recipient\RecipientDTO;
use ValeSaude\PaymentGatewayClient\ValueObjects\CreditCard;

class IuguGateway extends AbstractGateway
{
    private string $baseUrl;
    private string $apiKey;
    private bool $testMode;

    public function __construct(string $baseUrl, string $apiKey, bool $testMode)
    {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;
        $this->testMode = $testMode;
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

    public function createPaymentMethod(
        string $customerId,
        PaymentMethodDTO $data,
        bool $setAsDefault = true
    ): GatewayPaymentMethodDTO {
        $response = $this->doRequest(
            'POST',
            "v1/customers/{$customerId}/payment_methods",
            [
                'description' => $data->description,
                'token' => $data->token,
                'set_as_default' => $setAsDefault,
            ]
        );

        return new GatewayPaymentMethodDTO(
            $response->json('id'),
            new CreditCard(
                $response->json('data.holder_name'),
                $response->json('data.display_number'),
                $response->json('data.brand'),
                new Month($response->json('data.month')),
                new PositiveInteger($response->json('data.year'))
            )
        );
    }

    public function deletePaymentMethod(string $customerId, string $paymentMethodId): void
    {
        $this->doRequest(
            'DELETE',
            "v1/customers/{$customerId}/payment_methods/{$paymentMethodId}"
        );
    }

    public function createInvoice(
        ?string $customerId,
        InvoiceDTO $data,
        CustomerDTO $payer,
        string $externalReference
    ): GatewayInvoiceDTO {
        $builder = IuguInvoiceBuilder::make()
            ->fromInvoiceDTO($data)
            ->setExternalReference($externalReference)
            ->setPayer($payer);

        if (isset($customerId)) {
            $builder->setCustomerId($customerId);
        }

        $response = $this->doRequest(
            'POST',
            'v1/invoices',
            $builder->get()
        );

        return IuguAttributeConverter::convertInvoiceResponseToGatewayInvoiceDTO($response->json());
    }

    public function getInvoice(string $invoiceId): GatewayInvoiceDTO
    {
        $response = $this->doRequest('GET', "v1/invoices/{$invoiceId}");

        return IuguAttributeConverter::convertInvoiceResponseToGatewayInvoiceDTO($response->json());
    }

    public function chargeInvoiceUsingPaymentMethod(
        string $invoiceId,
        string $customerId,
        string $paymentMethodId,
        int $installments = 1
    ): void {
        $response = $this->doRequest(
            'POST',
            'v1/charge',
            [
                'invoice_id' => $invoiceId,
                'customer_id' => $customerId,
                'customer_payment_method_id' => $paymentMethodId,
                'months' => $installments,
            ]
        );

        if (!$response->json('success')) {
            if ($response->json('LR')) {
                throw TransactionDeclinedException::withLR($response->json('LR'));
            }

            // Por alguma razão, o Iugu retorna 200 mesmo quando ocorre um erro do tipo "esse token já foi usado"
            // Devido a isso, precisamos tratar também os "sucessos" como um possível erro
            $this->handleErrors($response);
        }
    }

    public function chargeInvoiceUsingToken(string $invoiceId, string $token, int $installments = 1): void
    {
        $response = $this->doRequest(
            'POST',
            'v1/charge',
            [
                'invoice_id' => $invoiceId,
                'token' => $token,
                'months' => $installments,
            ]
        );

        if (!$response->json('success')) {
            if ($response->json('LR')) {
                throw TransactionDeclinedException::withLR($response->json('LR'));
            }

            if ('token não é válido' === $response->json('errors')) {
                throw InvalidPaymentTokenException::invalidToken();
            }

            if ('Esse token já foi usado.' === $response->json('errors')) {
                throw InvalidPaymentTokenException::tokenAlreadyUsed();
            }

            // Por alguma razão, o Iugu retorna 200 mesmo quando ocorre um erro do tipo "esse token já foi usado"
            // Devido a isso, precisamos tratar também os "sucessos" como um possível erro
            $this->handleErrors($response);
        }
    }

    public function refundInvoice(string $invoiceId, ?Money $refundAmount = null): void
    {
        $this->doRequest(
            'POST',
            "v1/invoices/{$invoiceId}/refund",
            $refundAmount
                ? ['partial_value_refund_cents' => $refundAmount->getCents()]
                : []
        );
    }

    public function createRecipient(RecipientDTO $data): GatewayRecipientDTO
    {
        // TODO: Lidar com "ambiente" (esse recurso não funciona em ambiente dev)

        $createAccountResponse = $this->doRequest(
            'POST',
            'v1/marketplace/create_account',
            ['name' => $data->name]
        );

        $accountId = $createAccountResponse->json('account_id');
        $gatewaySpecificData = new JsonObject([
            'live_api_token' => $createAccountResponse->json('live_api_token'),
            'test_api_token' => $createAccountResponse->json('test_api_token'),
            'user_token' => $createAccountResponse->json('user_token'),
        ]);

        $this->doRequest(
            'POST',
            "v1/accounts/{$accountId}/request_verification",
            IuguRecipientBuilder::make()
                ->fromRecipientDTO($data)
                ->get(),
            $gatewaySpecificData->get('user_token')
        );

        return new GatewayRecipientDTO($accountId, RecipientStatus::PENDING(), $gatewaySpecificData);
    }

    public function subscribeWebhook(string $token): void
    {
        $this->doRequest(
            'POST',
            'v1/web_hooks',
            [
                'event' => 'all',
                'url' => route('webhooks.gateway', ['gateway' => $this->getGatewayIdentifier()]),
                'authorization' => $token,
            ]
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
    public function doRequest(
        string $method,
        string $uri,
        array $data = [],
        ?string $apiKey = null,
        bool $throwOnError = true
    ): Response {
        $pendingRequest = Http
            ::asJson()
            ->baseUrl($this->baseUrl)
            ->withToken(base64_encode($apiKey ?? $this->apiKey), 'Basic');

        if (!\in_array($method, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'])) {
            throw new InvalidArgumentException("Unsupported HTTP method {$method}.");
        }

        /** @var Response $response */
        $response = $pendingRequest->{strtolower($method)}($uri, $data);

        if ($throwOnError && $response->failed()) {
            $this->handleErrors($response);
        }

        return $response;
    }

    /**
     * @throws RequestException
     *
     * @return never
     */
    protected function handleErrors(Response $response): void
    {
        $errors = $response->json('errors');

        if (empty($errors)) {
            $response->throw();
        }

        $errors = Arr::wrap($errors);

        if (422 === $response->status()) {
            throw ValidationErrorResponseException::withErrors($errors);
        }

        throw GenericErrorResponseException::withErrors($errors);
    }

    public function getSupportedFeatures(): array
    {
        if ($this->testMode) {
            return array_diff(GatewayFeature::cases(), [GatewayFeature::RECIPIENT()]);
        }

        return GatewayFeature::cases();
    }
}
