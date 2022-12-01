<?php

namespace ValeSaude\PaymentGatewayClient\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;
use ValeSaude\PaymentGatewayClient\Customer\CustomerDTO;
use ValeSaude\PaymentGatewayClient\Database\Factories\CustomerFactory;
use ValeSaude\PaymentGatewayClient\Models\Concerns\GeneratesUUIDOnInitializeTrait;
use ValeSaude\PaymentGatewayClient\Models\Concerns\HasGatewayIdTrait;
use ValeSaude\PaymentGatewayClient\Models\Concerns\HasOwnerTrait;
use ValeSaude\PaymentGatewayClient\QueryBuilders\CustomerQueryBuilder;
use ValeSaude\PaymentGatewayClient\Recipient\Enums\DocumentType;
use ValeSaude\PaymentGatewayClient\ValueObjects\Address;
use ValeSaude\PaymentGatewayClient\ValueObjects\Document;
use ValeSaude\PaymentGatewayClient\ValueObjects\Email;

/**
 * @property string       $name
 * @property Document     $document
 * @property string       $document_number
 * @property DocumentType $document_type
 * @property Email        $email
 * @property Address      $address
 *
 * @method static CustomerQueryBuilder query()
 */
class Customer extends AbstractModel
{
    use GeneratesUUIDOnInitializeTrait;
    use HasFactory;
    use HasGatewayIdTrait;
    use HasOwnerTrait;

    protected $table = 'payment_gateway_customers';

    /**
     * @var array<string, class-string>
     */
    protected $casts = [
        'document' => Document::class,
        'document_type' => DocumentType::class,
        'email' => Email::class,
        'address' => Address::class,
    ];

    /**
     * @param Builder $query
     */
    public function newEloquentBuilder($query): CustomerQueryBuilder
    {
        return new CustomerQueryBuilder($query);
    }

    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function updateUsingCustomerDTO(CustomerDTO $data): self
    {
        $this->update([
            'name' => $data->name,
            'email' => $data->email,
            'document' => $data->document,
            'address' => $data->address,
        ]);

        return $this;
    }

    public function toCustomerDTO(): CustomerDTO
    {
        return new CustomerDTO(
            $this->name,
            $this->document,
            $this->email,
            $this->address
        );
    }

    public function getDefaultPaymentMethod(): ?PaymentMethod
    {
        return $this
            ->paymentMethods()
            ->whereIsDefault(1)
            ->first();
    }

    public static function fromCustomerDTO(CustomerDTO $data): self
    {
        return new self([
            'name' => $data->name,
            'email' => $data->email,
            'document' => $data->document,
            'address' => $data->address,
        ]);
    }

    protected static function newFactory(): CustomerFactory
    {
        return CustomerFactory::new();
    }
}
