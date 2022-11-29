<?php

namespace ValeSaude\PaymentGatewayClient;

use ValeSaude\PaymentGatewayClient\Customer\CustomerDTO;
use ValeSaude\PaymentGatewayClient\Gateways\Contracts\GatewayInterface;
use ValeSaude\PaymentGatewayClient\Models\Customer;

class Client
{
    private GatewayInterface $client;

    public function __construct(GatewayInterface $client)
    {
        $this->client = $client;
    }

    public function createCustomer(CustomerDTO $data): Customer
    {
        $customer = Customer::fromCustomerDTO($data);

        $id = $this->client->createCustomer($data, $customer->id);

        $customer->gateway_id = $id;
        $customer->gateway_slug = $this->client->getGatewayIdentifier();

        return tap($customer)->save();
    }
}
