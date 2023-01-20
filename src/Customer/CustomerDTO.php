<?php

namespace ValeSaude\PaymentGatewayClient\Customer;

use ValeSaude\LaravelValueObjects\Address;
use ValeSaude\LaravelValueObjects\Document;
use ValeSaude\LaravelValueObjects\Email;

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
