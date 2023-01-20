<?php

namespace ValeSaude\PaymentGatewayClient\Recipient;

use ValeSaude\LaravelValueObjects\Address;
use ValeSaude\LaravelValueObjects\BankAccount;
use ValeSaude\LaravelValueObjects\Document;
use ValeSaude\LaravelValueObjects\JsonObject;
use ValeSaude\LaravelValueObjects\Phone;

class RecipientDTO
{
    public string $name;
    public Document $document;
    public Address $address;
    public Phone $phone;
    public BankAccount $bankAccount;
    public bool $automaticWithdrawal;
    public JsonObject $gatewaySpecificData;
    public ?RepresentativeDTO $representative;

    public function __construct(
        string $name,
        Document $document,
        Address $address,
        Phone $phone,
        BankAccount $bankAccount,
        bool $automaticWithdrawal,
        JsonObject $gatewaySpecificData,
        ?RepresentativeDTO $representative = null
    ) {
        $this->name = $name;
        $this->document = $document;
        $this->address = $address;
        $this->phone = $phone;
        $this->bankAccount = $bankAccount;
        $this->automaticWithdrawal = $automaticWithdrawal;
        $this->gatewaySpecificData = $gatewaySpecificData;
        $this->representative = $representative;
    }
}
