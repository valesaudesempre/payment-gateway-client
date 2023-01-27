<?php

namespace ValeSaude\PaymentGatewayClient\Gateways\Fake;

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use ValeSaude\LaravelValueObjects\JsonObject;
use ValeSaude\LaravelValueObjects\Money;
use ValeSaude\LaravelValueObjects\Month;
use ValeSaude\LaravelValueObjects\PositiveInteger;
use ValeSaude\PaymentGatewayClient\Customer\CustomerDTO;
use ValeSaude\PaymentGatewayClient\Customer\GatewayPaymentMethodDTO;
use ValeSaude\PaymentGatewayClient\Customer\PaymentMethodDTO;
use ValeSaude\PaymentGatewayClient\Gateways\Contracts\GatewayInterface;
use ValeSaude\PaymentGatewayClient\Gateways\Enums\GatewayFeature;
use ValeSaude\PaymentGatewayClient\Gateways\Exceptions\GatewayException;
use ValeSaude\PaymentGatewayClient\Invoice\Collections\GatewayInvoiceItemDTOCollection;
use ValeSaude\PaymentGatewayClient\Invoice\Enums\InvoiceStatus;
use ValeSaude\PaymentGatewayClient\Invoice\GatewayInvoiceDTO;
use ValeSaude\PaymentGatewayClient\Invoice\GatewayInvoiceItemDTO;
use ValeSaude\PaymentGatewayClient\Invoice\InvoiceDTO;
use ValeSaude\PaymentGatewayClient\Invoice\InvoiceItemDTO;
use ValeSaude\PaymentGatewayClient\Recipient\Enums\RecipientStatus;
use ValeSaude\PaymentGatewayClient\Recipient\GatewayRecipientDTO;
use ValeSaude\PaymentGatewayClient\Recipient\RecipientDTO;
use ValeSaude\PaymentGatewayClient\ValueObjects\CreditCard;

/**
 * @codeCoverageIgnore
 */
class FakeGateway implements GatewayInterface
{
    private string $originalGatewayId;

    /** @var array<string, array{external_reference: string, original: CustomerDTO, data: CustomerDTO}> */
    private array $customers = [];

    /** @var array<string, array<string, array{is_default: bool, data: GatewayPaymentMethodDTO}>> */
    private array $paymentMethods = [];

    /** @var array<string, array<string, array{
     *     external_reference_id: string,
     *     token: string|null,
     *     payment_method_id: string|null,
     *     data: GatewayInvoiceDTO,
     *     payer: CustomerDTO
     * }>>
     */
    private array $invoices = [];

    /** @var array<string, array{data: RecipientDTO, status: RecipientStatus}> */
    private array $recipients = [];

    public function __construct(string $originalGatewayId)
    {
        $this->originalGatewayId = $originalGatewayId;
    }

    public function createCustomer(CustomerDTO $data, string $externalReference): string
    {
        $id = $this->generateResourceId();
        $this->customers[$id] = [
            'external_reference' => $externalReference,
            'original' => $data,
            'data' => $data,
        ];

        return $id;
    }

    public function updateCustomer(string $id, CustomerDTO $data): void
    {
        if (!isset($this->customers[$id])) {
            throw new GatewayException('Invalid customer id.');
        }

        $this->customers[$id]['data'] = $data;
    }

    public function createPaymentMethod(
        string $customerId,
        PaymentMethodDTO $data,
        bool $setAsDefault = true
    ): GatewayPaymentMethodDTO {
        if (!isset($this->customers[$customerId])) {
            throw new GatewayException('Invalid customer id.');
        }

        $id = $this->generateResourceId();

        $this->paymentMethods[$customerId][$id] = [
            'is_default' => false,
            'data' => new GatewayPaymentMethodDTO(
                $id,
                new CreditCard(
                    'Some Name',
                    'XXXX-XXXX-XXXX-1111',
                    'visa',
                    new Month('12'),
                    new PositiveInteger('99')
                )
            ),
        ];

        if ($setAsDefault) {
            foreach ($this->paymentMethods[$customerId] as &$paymentMethod) {
                $paymentMethod['is_default'] = false;
            }

            unset($paymentMethod);

            $this->paymentMethods[$customerId][$id]['is_default'] = true;
        }

        return $this->paymentMethods[$customerId][$id]['data'];
    }

    public function deletePaymentMethod(string $customerId, string $paymentMethodId): void
    {
        if (!isset($this->paymentMethods[$customerId][$paymentMethodId])) {
            throw new GatewayException('Invalid payment method id.');
        }

        unset($this->paymentMethods[$customerId][$paymentMethodId]);
    }

    public function createInvoice(
        ?string $customerId,
        InvoiceDTO $data,
        CustomerDTO $payer,
        string $externalReference
    ): GatewayInvoiceDTO {
        if (!isset($this->customers[$customerId])) {
            throw new GatewayException('Invalid customer id.');
        }

        $id = $this->generateResourceId();
        $items = new GatewayInvoiceItemDTOCollection(
            $data->items->map(function (InvoiceItemDTO $item) {
                $gatewayItem = GatewayInvoiceItemDTO::fromInvoiceItemDTO($item);
                $gatewayItem->id = $this->generateResourceId();

                return $gatewayItem;
            })
        );
        $invoice = new GatewayInvoiceDTO(
            $id,
            "https://some.url/invoice/{$id}",
            $data->dueDate,
            InvoiceStatus::PENDING(),
            $items,
            null,
            "some-bank-slip-code-{$id}",
            "some-pix-code-{$id}"
        );

        $this->invoices[$customerId][$id] = [
            'data' => $invoice,
            'external_reference' => $externalReference,
            'payer' => $payer,
            'payment_method_id' => null,
            'token' => null,
            'installments' => null,
        ];

        return $invoice;
    }

    public function getInvoice(string $invoiceId): GatewayInvoiceDTO
    {
        $customerId = null;

        foreach ($this->invoices as $invoiceCustomerId => $invoices) {
            if (isset($invoices[$invoiceId])) {
                $customerId = $invoiceCustomerId;
                break;
            }
        }

        if (!isset($customerId)) {
            throw new GatewayException('Invalid invoice id.');
        }

        return $this->invoices[$customerId][$invoiceId]['data'];
    }

    public function chargeInvoiceUsingPaymentMethod(
        string $invoiceId,
        string $customerId,
        string $paymentMethodId,
        int $installments = 1
    ): void {
        if (!isset($this->invoices[$customerId][$invoiceId])) {
            throw new GatewayException('Invalid invoice id.');
        }

        if (!isset($this->paymentMethods[$customerId][$paymentMethodId])) {
            throw new GatewayException('Invalid payment method id.');
        }

        $this->invoices[$customerId][$invoiceId]['data']->status = InvoiceStatus::PAID();
        $this->invoices[$customerId][$invoiceId]['data']->paidAt = CarbonImmutable::now();
        $this->invoices[$customerId][$invoiceId]['data']->installments = $installments;
        $this->invoices[$customerId][$invoiceId]['payment_method_id'] = $paymentMethodId;
    }

    public function chargeInvoiceUsingToken(string $invoiceId, string $token, int $installments = 1): void
    {
        $customerId = null;

        foreach ($this->invoices as $invoiceCustomerId => $invoices) {
            if (isset($invoices[$invoiceId])) {
                $customerId = $invoiceCustomerId;
                break;
            }
        }

        if (!isset($customerId)) {
            throw new GatewayException('Invalid invoice id.');
        }

        $this->invoices[$customerId][$invoiceId]['data']->status = InvoiceStatus::PAID();
        $this->invoices[$customerId][$invoiceId]['data']->paidAt = CarbonImmutable::now();
        $this->invoices[$customerId][$invoiceId]['data']->installments = $installments;
        $this->invoices[$customerId][$invoiceId]['token'] = $token;
    }

    public function refundInvoice(string $invoiceId, ?Money $refundValue = null): void
    {
        $customerId = null;

        foreach ($this->invoices as $invoiceCustomerId => $invoices) {
            if (isset($invoices[$invoiceId])) {
                $customerId = $invoiceCustomerId;
                break;
            }
        }

        if (!isset($customerId)) {
            throw new GatewayException('Invalid invoice id.');
        }

        $invoice = $this->invoices[$customerId][$invoiceId]['data'];
        $total = array_sum($invoice->items->map(fn (GatewayInvoiceItemDTO $item) => $item->price->getCents()));

        if (!$invoice->status->equals(InvoiceStatus::PAID())) {
            throw new GatewayException('The invoice must be paid.');
        }

        $this->invoices[$customerId][$invoiceId]['data']->status = InvoiceStatus::REFUNDED();
        $this->invoices[$customerId][$invoiceId]['data']->refundedAt = CarbonImmutable::now();
        $this->invoices[$customerId][$invoiceId]['data']->refundedAmount = $refundValue ?? new Money($total);
    }

    public function createRecipient(RecipientDTO $data): GatewayRecipientDTO
    {
        $id = (string) Str::uuid();
        $status = RecipientStatus::APPROVED();

        $this->recipients[$id]['data'] = $data;
        $this->recipients[$id]['status'] = $status;

        return new GatewayRecipientDTO($id, $status, JsonObject::empty());
    }

    public function getGatewayIdentifier(): string
    {
        return $this->originalGatewayId;
    }

    public function getSupportedFeatures(): array
    {
        return GatewayFeature::cases();
    }

    public function isFeatureSupported(GatewayFeature $feature): bool
    {
        return true;
    }

    /**
     * @return array<string, array{external_reference: string, original: CustomerDTO, data: CustomerDTO}>
     */
    public function getCustomers(): array
    {
        return $this->customers;
    }

    /**
     * @return array<string, array<string, array{is_default: bool, data: GatewayPaymentMethodDTO}>>
     */
    public function getPaymentMethods(): array
    {
        return $this->paymentMethods;
    }

    /**
     * @return array<string, array<string, array{
     *     external_reference_id: string,
     *     token: string|null,
     *     payment_method_id: string|null,
     *     data: GatewayInvoiceDTO,
     *     payer: CustomerDTO
     * }>>
     */
    public function getInvoices(): array
    {
        return $this->invoices;
    }

    /**
     * @return array<string, array{data: RecipientDTO, status: RecipientStatus}>
     */
    public function getRecipients(): array
    {
        return $this->recipients;
    }

    private function generateResourceId(): string
    {
        return Str::uuid();
    }
}
