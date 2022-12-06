<?php

namespace ValeSaude\PaymentGatewayClient\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use ValeSaude\PaymentGatewayClient\Customer\PaymentMethodDTO;
use ValeSaude\PaymentGatewayClient\Database\Factories\PaymentMethodFactory;
use ValeSaude\PaymentGatewayClient\Models\Concerns\GeneratesUUIDOnInitializeTrait;
use ValeSaude\PaymentGatewayClient\Models\Concerns\HasGatewayIdTrait;
use ValeSaude\PaymentGatewayClient\ValueObjects\CreditCard;

/**
 * @property string     $id
 * @property string     $description
 * @property CreditCard $card
 * @property bool       $is_default
 * @property string     $customer_id
 */
class PaymentMethod extends AbstractModel
{
    use GeneratesUUIDOnInitializeTrait;
    use HasFactory;
    use HasGatewayIdTrait;
    use SoftDeletes;

    protected $table = 'payment_gateway_payment_methods';

    /**
     * @var array<string, mixed>
     */
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
        // @phpstan-ignore-next-line
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
