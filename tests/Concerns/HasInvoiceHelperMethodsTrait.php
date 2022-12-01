<?php

namespace ValeSaude\PaymentGatewayClient\Tests\Concerns;

use Carbon\CarbonImmutable;
use ValeSaude\PaymentGatewayClient\Collections\InvoiceSplitRuleCollection;
use ValeSaude\PaymentGatewayClient\Invoice\Builders\InvoiceBuilder;
use ValeSaude\PaymentGatewayClient\Invoice\Collections\GatewayInvoiceItemDTOCollection;
use ValeSaude\PaymentGatewayClient\Invoice\Collections\InvoiceItemDTOCollection;
use ValeSaude\PaymentGatewayClient\Invoice\Collections\InvoicePaymentMethodCollection;
use ValeSaude\PaymentGatewayClient\Invoice\Enums\InvoicePaymentMethod;
use ValeSaude\PaymentGatewayClient\Invoice\GatewayInvoiceItemDTO;
use ValeSaude\PaymentGatewayClient\Invoice\InvoiceDTO;
use ValeSaude\PaymentGatewayClient\Invoice\InvoiceItemDTO;
use ValeSaude\PaymentGatewayClient\Models\Invoice;
use ValeSaude\PaymentGatewayClient\Models\InvoiceItem;
use ValeSaude\PaymentGatewayClient\Tests\TestCase;
use ValeSaude\PaymentGatewayClient\ValueObjects\InvoiceSplitRule;
use ValeSaude\PaymentGatewayClient\ValueObjects\Money;

/**
 * @mixin TestCase
 */
trait HasInvoiceHelperMethodsTrait
{
    public function createInvoiceDTO(): InvoiceDTO
    {
        return InvoiceBuilder::make()
            ->setDueDate(CarbonImmutable::now()->addDay())
            ->setAvailablePaymentMethods(...InvoicePaymentMethod::cases())
            ->setMaxInstallments(12)
            ->addItem(new InvoiceItemDTO(new Money(1234), 1, 'Some item'))
            ->get();
    }

    public function expectInvoiceToBeEqualsToData(Invoice $invoice, InvoiceDTO $data): void
    {
        $expectedPaymentMethods = $data->availablePaymentMethods ?? new InvoicePaymentMethodCollection(InvoicePaymentMethod::cases());
        $expectedSplits = $data->splits ?? new InvoiceSplitRuleCollection();

        expect($invoice->due_date->toDateString())->toEqual($data->dueDate->toDateString())
            ->and($invoice->max_installments)->toEqual($data->maxInstallments)
            ->and($invoice->available_payment_methods)->toHaveCount($expectedPaymentMethods->count())
            ->and($invoice->splits)->toHaveCount($expectedSplits->count());

        foreach ($expectedPaymentMethods as $paymentMethod) {
            $invoiceContainsPaymentMethod = $invoice->available_payment_methods->contains(
                fn (InvoicePaymentMethod $invoicePaymentMethod) => $invoicePaymentMethod->equals($paymentMethod)
            );

            expect($invoiceContainsPaymentMethod)->toBeTrue();
        }

        foreach ($expectedSplits as $split) {
            $invoiceContainsSplit = $invoice->splits->contains(
                fn (InvoiceSplitRule $invoiceSplit) => $invoiceSplit->equals($split)
            );

            expect($invoiceContainsSplit)->toBeTrue();
        }
    }

    public function expectInvoiceToContainAllGatewayItems(Invoice $invoice, GatewayInvoiceItemDTOCollection $items): void
    {
        /** @var GatewayInvoiceItemDTO $item */
        foreach ($items as $item) {
            $invoiceContainsItem = $invoice->items->contains(static function (InvoiceItem $invoiceItem) use ($item) {
                return $invoiceItem->gateway_id === $item->id &&
                    $invoiceItem->description === $item->description &&
                    $invoiceItem->price->equals($item->price) &&
                    $invoiceItem->quantity === $item->quantity;
            });

            expect($invoiceContainsItem)->toBeTrue();
        }
    }

    public function expectInvoiceToContainAllItems(Invoice $invoice, InvoiceItemDTOCollection $items): void
    {
        /** @var InvoiceItemDTO $item */
        foreach ($items as $item) {
            $invoiceContainsItem = $invoice->items->contains(static function (InvoiceItem $invoiceItem) use ($item) {
                return $invoiceItem->description === $item->description &&
                    $invoiceItem->price->equals($item->price) &&
                    $invoiceItem->quantity === $item->quantity;
            });

            expect($invoiceContainsItem)->toBeTrue();
        }
    }
}
