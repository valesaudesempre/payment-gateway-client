<?php

namespace ValeSaude\PaymentGatewayClient\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ValeSaude\PaymentGatewayClient\Customer\PaymentMethodDTO;
use ValeSaude\PaymentGatewayClient\Database\Factories\PaymentMethodFactory;
use ValeSaude\PaymentGatewayClient\Models\Concerns\GeneratesUUIDOnInitializeTrait;
use ValeSaude\PaymentGatewayClient\ValueObjects\CreditCard;

class PaymentMethod extends Model
{
    use GeneratesUUIDOnInitializeTrait;
    use HasFactory;

    public $incrementing = false;
    protected $table = 'payment_gateway_payment_methods';
    protected $guarded = [];
    protected $keyType = 'string';
    protected $attributes = [
        'is_default' => false,
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_default' => 'bool',
        'card' => CreditCard::class,
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function setAsDefault(): void
    {
        $this->customer
            ->paymentMethods()
            ->update(['is_default' => false]);

        $this->update(['is_default' => true]);
    }

    public static function fromPaymentMethodDTO(PaymentMethodDTO $data): self
    {
        return new self([
            'description' => $data->description,
        ]);
    }

    protected static function newFactory(): PaymentMethodFactory
    {
        return PaymentMethodFactory::new();
    }
}
