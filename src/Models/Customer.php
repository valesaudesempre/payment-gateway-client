<?php

namespace ValeSaude\PaymentGatewayClient\Models;

use Illuminate\Database\Eloquent\Model;
use ValeSaude\PaymentGatewayClient\Customer\CustomerDTO;
use ValeSaude\PaymentGatewayClient\Models\Concerns\GeneratesUUIDOnInitializeTrait;
use ValeSaude\PaymentGatewayClient\ValueObjects\Address;
use ValeSaude\PaymentGatewayClient\ValueObjects\CPF;
use ValeSaude\PaymentGatewayClient\ValueObjects\Email;

/**
 *
 */
class Customer extends Model
{
    use GeneratesUUIDOnInitializeTrait;

    public $incrementing = false;
    protected $table = 'payment_gateway_customers';
    protected $guarded = [];
    protected $keyType = 'string';

    /**
     * @var array<string, class-string>
     */
    protected $casts = [
        'document_number' => CPF::class,
        'email' => Email::class,
        'address' => Address::class,
    ];

    public function toCustomerDTO(): CustomerDTO
    {
        return new CustomerDTO(
            $this->name,
            $this->document_number,
            $this->email,
            $this->address
        );
    }

    public static function fromCustomerDTO(CustomerDTO $data): self
    {
        return new self([
            'name' => $data->name,
            'email' => $data->email,
            'document_number' => $data->documentNumber,
            'address' => $data->address,
        ]);
    }
}
