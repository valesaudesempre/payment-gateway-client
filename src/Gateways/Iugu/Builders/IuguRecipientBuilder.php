<?php

namespace ValeSaude\PaymentGatewayClient\Gateways\Iugu\Builders;

use ValeSaude\PaymentGatewayClient\Enums\DocumentType;
use ValeSaude\PaymentGatewayClient\Gateways\Iugu\Utils\IuguAttributeConverter;
use ValeSaude\PaymentGatewayClient\Recipient\RecipientDTO;
use ValeSaude\PaymentGatewayClient\ValueObjects\Address;
use ValeSaude\PaymentGatewayClient\ValueObjects\BankAccount;
use ValeSaude\PaymentGatewayClient\ValueObjects\Document;

class IuguRecipientBuilder extends AbstractIuguBuilder
{
    public function setAccountOwner(string $name, Document $document): self
    {
        if ($document->getType()->equals(DocumentType::CPF())) {
            $this->properties['name'] = $name;
            $this->properties['person_type'] = 'Pessoa Física';
            $this->properties['cpf'] = $document->getNumber();
        } else {
            $this->properties['company_name'] = $name;
            $this->properties['person_type'] = 'Pessoa Jurídica';
            $this->properties['cnpj'] = $document->getNumber();
        }

        return $this;
    }

    public function setAddress(Address $address): self
    {
        $addressString = "{$address->getStreet()}, {$address->getNumber()}";

        if ($complement = $address->getComplement()) {
            $addressString .= " - {$complement}";
        }

        $this->properties = array_merge($this->properties, [
            'cep' => (string) $address->getZipCode(),
            'address' => $addressString,
            'district' => $address->getDistrict(),
            'city' => $address->getCity(),
            'state' => $address->getState(),
        ]);

        return $this;
    }

    public function setRepresentative(string $name, Document $document): self
    {
        $this->properties['resp_name'] = $name;
        $this->properties['resp_cpf'] = $document->getNumber();

        return $this;
    }

    public function setBankAccount(BankAccount $bankAccount): self
    {
        $this->properties = array_merge($this->properties, [
            'bank' => $bankAccount->getBank()->getCode(),
            'bank_ag' => $bankAccount->getAgencyFormatted(),
            'account_type' => IuguAttributeConverter::convertBankAccountTypeToIuguAccountType($bankAccount->getType()),
            'bank_cc' => $bankAccount->getAccountFormatted(),
        ]);

        return $this;
    }

    public function fromRecipientDTO(RecipientDTO $data): self
    {
        $this->properties = array_merge($this->properties, [
            'price_range' => $data->gatewaySpecificData->get('price_range'),
            'physical_products' => $data->gatewaySpecificData->get('physical_products'),
            'business_type' => $data->gatewaySpecificData->get('business_type'),
            'telephone' => (string) $data->phone,
            'automatic_transfer' => $data->automaticWithdrawal,
        ]);

        $this->setAccountOwner($data->name, $data->document)
            ->setAddress($data->address)
            ->setBankAccount($data->bankAccount);

        if ($data->representative) {
            $this->setRepresentative($data->representative->name, $data->representative->document);
        }

        return $this;
    }
}
