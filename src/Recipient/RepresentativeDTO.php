<?php

namespace ValeSaude\PaymentGatewayClient\Recipient;

use ValeSaude\PaymentGatewayClient\ValueObjects\Document;

class RepresentativeDTO
{
    public string $name;
    public Document $document;

    public function __construct(string $name, Document $document)
    {
        $this->name = $name;
        $this->document = $document;
    }
}
