<?php

namespace ValeSaude\PaymentGatewayClient\Recipient\Builders;

use ValeSaude\PaymentGatewayClient\Recipient\RecipientDTO;
use ValeSaude\PaymentGatewayClient\Recipient\RepresentativeDTO;
use ValeSaude\PaymentGatewayClient\ValueObjects\Address;
use ValeSaude\PaymentGatewayClient\ValueObjects\BankAccount;
use ValeSaude\PaymentGatewayClient\ValueObjects\Document;
use ValeSaude\PaymentGatewayClient\ValueObjects\JsonObject;
use ValeSaude\PaymentGatewayClient\ValueObjects\Phone;

class RecipientBuilder
{
    private string $name;
    private Document $document;
    private Address $address;
    private Phone $phone;
    private BankAccount $bankAccount;
    private bool $automaticWithdrawal;
    private ?RepresentativeDTO $representative = null;
    private ?JsonObject $gatewaySpecificData = null;

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setDocument(Document $document): self
    {
        $this->document = $document;

        return $this;
    }

    public function setAddress(Address $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function setPhone(Phone $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function setBankAccount(BankAccount $account): self
    {
        $this->bankAccount = $account;

        return $this;
    }

    public function setAutomaticWithdrawal(bool $automaticWithdrawal): self
    {
        $this->automaticWithdrawal = $automaticWithdrawal;

        return $this;
    }

    public function setRepresentative(string $name, Document $document): self
    {
        $this->representative = new RepresentativeDTO($name, $document);

        return $this;
    }

    public function setGatewaySpecificData(JsonObject $data): self
    {
        $this->gatewaySpecificData = $data;

        return $this;
    }

    public function get(): RecipientDTO
    {
        return new RecipientDTO(
            $this->name,
            $this->document,
            $this->address,
            $this->phone,
            $this->bankAccount,
            $this->automaticWithdrawal,
            $this->gatewaySpecificData ?? JsonObject::empty(),
            $this->representative
        );
    }

    public static function make(): self
    {
        return new self();
    }
}
