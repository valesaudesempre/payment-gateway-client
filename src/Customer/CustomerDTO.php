<?php

namespace ValeSaude\PaymentGatewayClient\Customer;

use ValeSaude\PaymentGatewayClient\ValueObjects\Address;
use ValeSaude\PaymentGatewayClient\ValueObjects\CPF;
use ValeSaude\PaymentGatewayClient\ValueObjects\Email;

class CustomerDTO
{
    public string $name;
    public CPF $documentNumber;
    public Email $email;
    public Address $address;

    public function __construct(string $name, CPF $documentNumber, Email $email, Address $address)
    {
        $this->name = $name;
        $this->documentNumber = $documentNumber;
        $this->email = $email;
        $this->address = $address;
    }
}
