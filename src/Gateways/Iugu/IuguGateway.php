<?php

namespace ValeSaude\PaymentGatewayClient\Gateways\Iugu;

use Carbon\Carbon;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use ValeSaude\PaymentGatewayClient\Customer\CustomerDTO;
use ValeSaude\PaymentGatewayClient\Customer\GatewayPaymentMethodDTO;
use ValeSaude\PaymentGatewayClient\Customer\PaymentMethodDTO;
use ValeSaude\PaymentGatewayClient\Gateways\AbstractGateway;
use ValeSaude\PaymentGatewayClient\Gateways\Exceptions\TransactionDeclinedException;
use ValeSaude\PaymentGatewayClient\Gateways\Iugu\Builders\IuguCustomerBuilder;
use ValeSaude\PaymentGatewayClient\Gateways\Iugu\Builders\IuguInvoiceBuilder;
use ValeSaude\PaymentGatewayClient\Gateways\Iugu\Exceptions\GenericErrorResponseException;
use ValeSaude\PaymentGatewayClient\Gateways\Iugu\Exceptions\ValidationErrorResponseException;
use ValeSaude\PaymentGatewayClient\Gateways\Utils\AttributeConverter;
use ValeSaude\PaymentGatewayClient\Invoice\GatewayInvoiceDTO;
use ValeSaude\PaymentGatewayClient\Invoice\InvoiceDTO;
use ValeSaude\PaymentGatewayClient\ValueObjects\CreditCard;
use ValeSaude\PaymentGatewayClient\ValueObjects\Month;
use ValeSaude\PaymentGatewayClient\ValueObjects\PositiveInteger;

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

        return new GatewayInvoiceDTO(
            $response->json('id'),
            $response->json('secure_url'),
            // @phpstan-ignore-next-line
            Carbon::make($response->json('due_date')),
            AttributeConverter::convertIuguStatusToInvoiceStatus($response->json('status')),
            AttributeConverter::convertInvoiceItemsToGatewayInvoiceItemDTOCollection($response->json('items')),
            $response->json('bank_slip.digitable_line'),
            $response->json('pix.qrcode_text')
        );
    }

    public function chargeInvoiceUsingPaymentMethod(string $invoiceId, string $customerId, string $paymentMethodId): void
    {
        $response = $this->doRequest(
            'POST',
            'v1/charge',
            [
                'invoice_id' => $invoiceId,
                'customer_id' => $customerId,
                'customer_payment_method_id' => $paymentMethodId,
            ]
        );

        if (!$response->json('success')) {
            throw TransactionDeclinedException::withLR($response->json('LR'));
        }
    }

    public function chargeInvoiceUsingToken(string $invoiceId, string $token): void
    {
        $response = $this->doRequest(
            'POST',
            'v1/charge',
            [
                'invoice_id' => $invoiceId,
                'token' => $token,
            ]
        );

        if (!$response->json('success')) {
            throw TransactionDeclinedException::withLR($response->json('LR'));
        }
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

        if (422 === $response->status()) {
            throw ValidationErrorResponseException::withErrors($errors);
        }

        throw GenericErrorResponseException::withErrors($errors);
    }
}
