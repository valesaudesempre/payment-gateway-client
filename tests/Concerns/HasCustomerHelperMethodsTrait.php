<?php

namespace ValeSaude\PaymentGatewayClient\Tests\Concerns;

use ValeSaude\PaymentGatewayClient\Customer\CustomerDTO;
use ValeSaude\PaymentGatewayClient\Models\Customer;
use ValeSaude\PaymentGatewayClient\ValueObjects\Address;
use ValeSaude\PaymentGatewayClient\ValueObjects\CPF;
use ValeSaude\PaymentGatewayClient\ValueObjects\Email;
use ValeSaude\PaymentGatewayClient\ValueObjects\ZipCode;

trait HasCustomerHelperMethodsTrait
{
    public function createCustomerDTO(): CustomerDTO
    {
        return new CustomerDTO(
            'Some Name',
            new CPF('74406433058'),
            new Email('some@mail.com'),
            new Address(
                new ZipCode('01001000'),
                'Some Street',
                1,
                'Some District',
                'Some City',
                'SP'
            )
        );
    }

    public function expectCustomerToBeEqualsToData(Customer $customer, CustomerDTO $data): void
    {
        expect($customer->name)->toEqual($data->name)
            ->and($customer->document_number)->toEqual($data->documentNumber)
            ->and($customer->address)->toEqual($data->address);
    }
}
