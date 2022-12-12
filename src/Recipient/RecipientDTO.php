<?php

namespace ValeSaude\PaymentGatewayClient\Recipient;

use ValeSaude\PaymentGatewayClient\ValueObjects\Address;
use ValeSaude\PaymentGatewayClient\ValueObjects\BankAccount;
use ValeSaude\PaymentGatewayClient\ValueObjects\Document;
use ValeSaude\PaymentGatewayClient\ValueObjects\JsonObject;
use ValeSaude\PaymentGatewayClient\ValueObjects\Phone;

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
