<?php

namespace ValeSaude\PaymentGatewayClient\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use ValeSaude\PaymentGatewayClient\Customer\CustomerDTO;
use ValeSaude\PaymentGatewayClient\Database\Factories\CustomerFactory;
use ValeSaude\PaymentGatewayClient\Models\Concerns\GeneratesUUIDOnInitializeTrait;
use ValeSaude\PaymentGatewayClient\ValueObjects\Address;
use ValeSaude\PaymentGatewayClient\ValueObjects\CPF;
use ValeSaude\PaymentGatewayClient\ValueObjects\Email;

class Customer extends Model
{
    use GeneratesUUIDOnInitializeTrait;
    use HasFactory;

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

    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    public function updateUsingCustomerDTO(CustomerDTO $data): self
    {
        $this->update([
            'name' => $data->name,
            'email' => $data->email,
            'document_number' => $data->documentNumber,
            'address' => $data->address,
        ]);

        return $this;
    }

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

    protected static function newFactory(): CustomerFactory
    {
        return CustomerFactory::new();
    }
}
