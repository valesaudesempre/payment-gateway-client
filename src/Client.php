<?php

namespace ValeSaude\PaymentGatewayClient;

use ValeSaude\PaymentGatewayClient\Customer\CustomerDTO;
use ValeSaude\PaymentGatewayClient\Customer\PaymentMethodDTO;
use ValeSaude\PaymentGatewayClient\Exceptions\UnsupportedFeatureException;
use ValeSaude\PaymentGatewayClient\Gateways\Contracts\GatewayInterface;
use ValeSaude\PaymentGatewayClient\Gateways\Enums\GatewayFeature;
use ValeSaude\PaymentGatewayClient\Models\Customer;
use ValeSaude\PaymentGatewayClient\Models\PaymentMethod;

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
            // @phpstan-ignore-next-line
            $this->gateway->updateCustomer($customer->gateway_id, $data);
        }

        return $customer->updateUsingCustomerDTO($data);
    }

    public function createPaymentMethod(
        Customer $customer,
        PaymentMethodDTO $data,
        bool $setAsDefault = true
    ): PaymentMethod {
        $this->ensureFeatureIsSupported(GatewayFeature::PAYMENT_METHOD());

        $paymentMethod = PaymentMethod::fromPaymentMethodDTO($data);
        // @phpstan-ignore-next-line
        $gatewayPaymentMethod = $this->gateway->createPaymentMethod($customer->gateway_id, $data, $setAsDefault);

        $paymentMethod->card = $gatewayPaymentMethod->card;
        $paymentMethod->gateway_id = $gatewayPaymentMethod->id;
        $paymentMethod->gateway_slug = $this->gateway->getGatewayIdentifier();

        $customer->paymentMethods()->save($paymentMethod);

        if ($setAsDefault) {
            $paymentMethod->setAsDefault();
        }

        return $paymentMethod;
    }

    /**
     * @throws UnsupportedFeatureException
     */
    public function ensureFeatureIsSupported(GatewayFeature $feature): void
    {
        if (!$this->gateway->isFeatureSupported($feature)) {
            throw UnsupportedFeatureException::withFeatureAndGateway(
                $feature,
                $this->gateway->getGatewayIdentifier()
            );
        }
    }
}
