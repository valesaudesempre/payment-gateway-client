<?php

namespace ValeSaude\PaymentGatewayClient\Gateways\Iugu\Builders;

use ValeSaude\PaymentGatewayClient\Collections\InvoiceSplitRuleCollection;
use ValeSaude\PaymentGatewayClient\Customer\CustomerDTO;
use ValeSaude\PaymentGatewayClient\Gateways\Utils\AttributeConverter;
use ValeSaude\PaymentGatewayClient\Invoice\Collections\InvoiceItemDTOCollection;
use ValeSaude\PaymentGatewayClient\Invoice\Collections\InvoicePaymentMethodCollection;
use ValeSaude\PaymentGatewayClient\Invoice\Enums\InvoicePaymentMethod;
use ValeSaude\PaymentGatewayClient\Invoice\InvoiceDTO;
use ValeSaude\PaymentGatewayClient\Invoice\InvoiceItemDTO;
use ValeSaude\PaymentGatewayClient\ValueObjects\InvoiceSplitRule;

class IuguInvoiceBuilder extends AbstractIuguBuilder
{
    public function setExternalReference(string $externalReference): self
    {
        $this->properties['external_reference'] = $externalReference;

        return $this;
    }

    public function setCustomerId(string $customerId): self
    {
        $this->properties['customer_id'] = $customerId;

        return $this;
    }

    public function setItems(InvoiceItemDTOCollection $items): self
    {
        $invoiceItems = [];

        /** @var InvoiceItemDTO $item */
        foreach ($items as $item) {
            $invoiceItems[] = [
                'description' => $item->description,
                'quantity' => $item->quantity,
                'price_cents' => $item->price->getCents(),
            ];
        }

        $this->properties['items'] = $invoiceItems;

        return $this;
    }

    public function setSplits(InvoiceSplitRuleCollection $splits): self
    {
        $invoiceSplits = [];

        /** @var InvoiceSplitRule $split */
        foreach ($splits as $split) {
            $invoiceSplits[] = [
                'recipient_account_id' => $split->getRecipientId(),
                'cents' => $split->getAmount()->getCents(),
            ];
        }

        $this->properties['splits'] = $invoiceSplits;

        return $this;
    }

    public function setPaymentMethods(InvoicePaymentMethodCollection $methods): self
    {
        $invoicePaymentMethods = [];

        /** @var InvoicePaymentMethod $method */
        foreach ($methods as $method) {
            $invoicePaymentMethods[] = AttributeConverter::convertInvoicePaymentMethodToIuguPaymentMethod($method);
        }

        $this->properties['payable_with'] = $invoicePaymentMethods;

        return $this;
    }

    public function fromInvoiceDTO(InvoiceDTO $data): self
    {
        $this->properties = array_merge($this->properties, [
            'due_date' => $data->dueDate->toDateString(),
            'max_installments_value' => $data->maxInstallments,
        ]);

        if ($data->availablePaymentMethods) {
            $this->setPaymentMethods($data->availablePaymentMethods);
        }

        if ($data->splits) {
            $this->setSplits($data->splits);
        }

        return $this->setItems($data->items);
    }

    public function setPayer(CustomerDTO $payer): self
    {
        $this->properties['payer'] = [
            'cpf_cnpj' => $payer->document->getNumber(),
            'name' => $payer->name,
            'email' => (string) $payer->email,
            'address' => [
                'zip_code' => (string) $payer->address->getZipCode(),
                'street' => $payer->address->getStreet(),
                'number' => $payer->address->getNumber(),
                'district' => $payer->address->getDistrict(),
                'city' => $payer->address->getCity(),
                'state' => $payer->address->getState(),
                'complement' => $payer->address->getComplement(),
                'country' => 'Brasil',
            ],
        ];

        return $this;
    }
}
