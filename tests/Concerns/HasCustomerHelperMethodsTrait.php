<?php

namespace ValeSaude\PaymentGatewayClient\Tests\Concerns;

use ValeSaude\PaymentGatewayClient\Customer\CustomerDTO;
use ValeSaude\PaymentGatewayClient\Models\Customer;

trait HasCustomerHelperMethodsTrait
{
    public function createCustomerDTO(): CustomerDTO
    {
        return Customer::factory()->make()->toCustomerDTO();
    }

    public function expectCustomerToBeEqualsToData(Customer $customer, CustomerDTO $data): void
    {
        expect($customer->name)->toEqual($data->name)
            ->and($customer->email)->toEqual($data->email)
            ->and($customer->document_number)->toEqual($data->documentNumber)
            ->and($customer->address)->toEqual($data->address);
    }
}
