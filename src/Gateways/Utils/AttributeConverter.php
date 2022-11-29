<?php

namespace ValeSaude\PaymentGatewayClient\Gateways\Utils;

use InvalidArgumentException;
use ValeSaude\PaymentGatewayClient\Invoice\Collections\GatewayInvoiceItemDTOCollection;
use ValeSaude\PaymentGatewayClient\Invoice\Enums\InvoicePaymentMethod;
use ValeSaude\PaymentGatewayClient\Invoice\Enums\InvoiceStatus;
use ValeSaude\PaymentGatewayClient\Invoice\GatewayInvoiceItemDTO;
use ValeSaude\PaymentGatewayClient\ValueObjects\Money;

final class AttributeConverter
{
    /**
     * @throws InvalidArgumentException
     */
    public static function convertInvoicePaymentMethodToIuguPaymentMethod(InvoicePaymentMethod $method): string
    {
        switch (true) {
            case $method->equals(InvoicePaymentMethod::CREDIT_CARD()):
                return 'credit_card';
            case $method->equals(InvoicePaymentMethod::BANK_SLIP()):
                return 'bank_slip';
            case $method->equals(InvoicePaymentMethod::PIX()):
                return 'pix';
            default:
                throw new InvalidArgumentException("The payment method {$method->value} is not supported.");
        }
    }

    public static function convertIuguStatusToInvoiceStatus(string $status): InvoiceStatus
    {
        switch ($status) {
            case 'pending':
                return InvoiceStatus::PENDING();
            case 'paid':
                return InvoiceStatus::PAID();
            case 'canceled':
                return InvoiceStatus::CANCELED();
            case 'partially_refunded':
            case 'refunded':
                return InvoiceStatus::REFUNDED();
            case 'expired':
                return InvoiceStatus::EXPIRED();
            case 'authorized':
                return InvoiceStatus::AUTHORIZED();
            default:
                throw new InvalidArgumentException("The status {$status} is not valid.");
        }
    }

    /**
     * @param array<array-key, array{id: string, description: string, price_cents: int, quantity: int}> $items
     */
    public static function convertInvoiceItemsToGatewayInvoiceItemDTOCollection(array $items): GatewayInvoiceItemDTOCollection
    {
        $collection = new GatewayInvoiceItemDTOCollection();

        foreach ($items as $item) {
            $collection->add(
                new GatewayInvoiceItemDTO(
                    $item['id'],
                    new Money($item['price_cents']),
                    $item['quantity'],
                    $item['description']
                )
            );
        }

        return $collection;
    }
}
