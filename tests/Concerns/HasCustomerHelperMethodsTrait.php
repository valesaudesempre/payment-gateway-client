<?php

namespace ValeSaude\PaymentGatewayClient\Tests\Concerns;

use ValeSaude\LaravelValueObjects\Month;
use ValeSaude\LaravelValueObjects\PositiveInteger;
use ValeSaude\PaymentGatewayClient\Customer\CustomerDTO;
use ValeSaude\PaymentGatewayClient\Customer\PaymentMethodDTO;
use ValeSaude\PaymentGatewayClient\Models\Customer;
use ValeSaude\PaymentGatewayClient\Tests\TestCase;
use ValeSaude\PaymentGatewayClient\ValueObjects\CreditCard;

/**
 * @mixin TestCase
 */
trait HasCustomerHelperMethodsTrait
{
    public function createCustomerDTO(): CustomerDTO
    {
        return Customer::factory()->make()->toCustomerDTO();
    }

    public function createPaymentMethodDTO(): PaymentMethodDTO
    {
        return new PaymentMethodDTO(
            $this->faker->text(),
            $this->faker->uuid(),
        );
    }

    public function createCreditCard(): CreditCard
    {
        return new CreditCard(
            $this->faker->name(),
            'XXXX-XXXX-XXXX-1234',
            'visa',
            new Month(12),
            new PositiveInteger(2030)
        );
    }

    public function expectCustomerToBeEqualsToData(Customer $customer, CustomerDTO $data): void
    {
        expect($customer->name)->toEqual($data->name)
            ->and($customer->email)->toEqual($data->email)
            ->and($customer->document)->toEqual($data->document)
            ->and($customer->address)->toEqual($data->address);
    }
}
