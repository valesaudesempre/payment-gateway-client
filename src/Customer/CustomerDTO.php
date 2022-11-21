<?php

namespace ValeSaude\PaymentGatewayClient\Customer;

use ValeSaude\PaymentGatewayClient\ValueObjects\Address;
use ValeSaude\PaymentGatewayClient\ValueObjects\Document;
use ValeSaude\PaymentGatewayClient\ValueObjects\Email;

class CustomerDTO
{
    public string $name;
    public Document $document;
    public Email $email;
    public Address $address;

    public function __construct(string $name, Document $document, Email $email, Address $address)
    {
        $this->name = $name;
        $this->document = $document;
        $this->email = $email;
        $this->address = $address;
    }
}
