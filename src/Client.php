<?php

namespace ValeSaude\PaymentGatewayClient;

use ValeSaude\PaymentGatewayClient\Customer\CustomerDTO;
use ValeSaude\PaymentGatewayClient\Customer\PaymentMethodDTO;
use ValeSaude\PaymentGatewayClient\Gateways\Contracts\GatewayInterface;
use ValeSaude\PaymentGatewayClient\Gateways\Enums\GatewayFeature;
use ValeSaude\PaymentGatewayClient\Models\Customer;

class Client
{
    private GatewayInterface $gateway;

    public function __construct(GatewayInterface $client)
    {
        $this->gateway = $client;
    }

    public function createCustomer(CustomerDTO $data): Customer
    {
        $customer = Customer::fromCustomerDTO($data);
        $customer->gateway_slug = $this->gateway->getGatewayIdentifier();

        if ($this->gateway->isFeatureSupported(GatewayFeature::CUSTOMER())) {
            $id = $this->gateway->createCustomer($data, $customer->id);
            $customer->gateway_id = $id;
        }

        return tap($customer)->save();
    }

    public function updateCustomer(Customer $customer, CustomerDTO $data): Customer
    {
        if ($this->gateway->isFeatureSupported(GatewayFeature::CUSTOMER())) {
            $this->gateway->updateCustomer($customer->gateway_id, $data);
        }

        return $customer->updateUsingCustomerDTO($data);
    }
}
