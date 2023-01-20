<?php

namespace ValeSaude\PaymentGatewayClient\Invoice\Builders;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use ValeSaude\LaravelValueObjects\Money;
use ValeSaude\PaymentGatewayClient\Collections\InvoiceSplitRuleCollection;
use ValeSaude\PaymentGatewayClient\Invoice\Collections\InvoiceItemDTOCollection;
use ValeSaude\PaymentGatewayClient\Invoice\Collections\InvoicePaymentMethodCollection;
use ValeSaude\PaymentGatewayClient\Invoice\Enums\InvoicePaymentMethod;
use ValeSaude\PaymentGatewayClient\Invoice\InvoiceDTO;
use ValeSaude\PaymentGatewayClient\Invoice\InvoiceItemDTO;
use ValeSaude\PaymentGatewayClient\Models\Recipient;
use ValeSaude\PaymentGatewayClient\ValueObjects\InvoiceSplitRule;

class InvoiceBuilder
{
    private CarbonInterface $dueDate;
    private int $maxInstallments;
    private InvoicePaymentMethodCollection $availablePaymentMethods;
    private InvoiceSplitRuleCollection $splits;
    private InvoiceItemDTOCollection $items;

    private function __construct()
    {
        $this->dueDate = CarbonImmutable::today();
        $this->maxInstallments = 1;
        $this->availablePaymentMethods = new InvoicePaymentMethodCollection(InvoicePaymentMethod::cases());
        $this->splits = new InvoiceSplitRuleCollection();
        $this->items = new InvoiceItemDTOCollection();
    }

    public function setDueDate(CarbonInterface $dueDate): self
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function setMaxInstallments(int $maxInstallments): self
    {
        $this->maxInstallments = $maxInstallments;

        return $this;
    }

    public function setAvailablePaymentMethods(InvoicePaymentMethod ...$method): self
    {
        $this->availablePaymentMethods = new InvoicePaymentMethodCollection($method);

        return $this;
    }

    public function addItem(InvoiceItemDTO $item): self
    {
        $this->items->add($item);

        return $this;
    }

    public function addSplit(Recipient $recipient, Money $amount): self
    {
        // @phpstan-ignore-next-line
        $this->splits->add(new InvoiceSplitRule($recipient->gateway_id, $amount));

        return $this;
    }

    public function get(): InvoiceDTO
    {
        return new InvoiceDTO(
            $this->dueDate,
            $this->items,
            $this->maxInstallments,
            $this->availablePaymentMethods,
            $this->splits
        );
    }

    public static function make(): self
    {
        return new self();
    }
}
