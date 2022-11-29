<?php

namespace ValeSaude\PaymentGatewayClient\Gateways\Iugu\Builders;

use ValeSaude\PaymentGatewayClient\Customer\CustomerDTO;

class IuguCustomerBuilder extends AbstractIuguBuilder
{
    public function fromCustomerDTO(CustomerDTO $data): self
    {
        $this->properties = array_merge($this->properties, [
            'email' => (string) $data->email,
            'name' => $data->name,
            'cpf_cnpj' => (string) $data->documentNumber,
            'zip_code' => (string) $data->address->getZipCode(),
            'number' => $data->address->getNumber(),
            'street' => $data->address->getStreet(),
            'city' => $data->address->getCity(),
            'state' => $data->address->getState(),
            'district' => $data->address->getDistrict(),
            'complement' => $data->address->getComplement(),
        ]);

        return $this;
    }
}
