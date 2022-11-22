<?php

namespace ValeSaude\PaymentGatewayClient\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use ValeSaude\PaymentGatewayClient\Casts\InvoicePaymentMethodCollectionCast;
use ValeSaude\PaymentGatewayClient\Casts\InvoiceSplitRuleCollectionCast;
use ValeSaude\PaymentGatewayClient\Collections\InvoiceSplitRuleCollection;
use ValeSaude\PaymentGatewayClient\Database\Factories\InvoiceFactory;
use ValeSaude\PaymentGatewayClient\Invoice\Collections\InvoicePaymentMethodCollection;
use ValeSaude\PaymentGatewayClient\Invoice\Enums\InvoicePaymentMethod;
use ValeSaude\PaymentGatewayClient\Invoice\Enums\InvoiceStatus;
use ValeSaude\PaymentGatewayClient\Invoice\InvoiceDTO;
use ValeSaude\PaymentGatewayClient\Models\Concerns\GeneratesUUIDOnInitializeTrait;
use ValeSaude\PaymentGatewayClient\ValueObjects\Money;

/**
 * @property string|null                    $url
 * @property Carbon                         $due_date
 * @property InvoicePaymentMethodCollection $available_payment_methods
 * @property int                            $max_installments
 * @property InvoiceStatus                  $status
 * @property Money                          $total
 * @property Carbon|null                    $paid_at
 * @property Carbon|null                    $canceled_at
 * @property Carbon|null                    $refunded_at
 * @property Money|null                     $refunded_amount
 * @property string|null                    $bank_slip_code
 * @property string|null                    $pix_code
 * @property string|null                    $gateway_id
 * @property string                         $gateway_slug
 */
class Invoice extends AbstractModel
{
    use GeneratesUUIDOnInitializeTrait;
    use HasFactory;

    protected $table = 'payment_gateway_invoices';

    /**
     * @var array<string, class-string|string>
     */
    protected $casts = [
        'due_date' => 'date',
        'max_installments' => 'int',
        'status' => InvoiceStatus::class,
        'available_payment_methods' => InvoicePaymentMethodCollectionCast::class,
        'splits' => InvoiceSplitRuleCollectionCast::class,
        'paid_at' => 'datetime',
        'canceled_at' => 'datetime',
        'refunded_at' => 'datetime',
        'refunded_amount' => Money::class,
    ];

    public function getTotalAttribute(): Money
    {
        $total = $this->items->sum(
            fn (InvoiceItem $item) => $item->price->multiply($item->quantity)->getCents()
        );

        return new Money($total);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function markAsPaid(): void
    {
        $this->update([
            'status' => InvoiceStatus::PAID(),
            'paid_at' => Carbon::now(),
        ]);
    }

    public static function fromInvoiceDTO(InvoiceDTO $data): self
    {
        return new self([
            'max_installments' => $data->maxInstallments,
            'available_payment_methods' => $data->availablePaymentMethods ?? new InvoicePaymentMethodCollection(InvoicePaymentMethod::cases()),
            'splits' => $data->splits ?? new InvoiceSplitRuleCollection(),
        ]);
    }

    public static function newFactory(): InvoiceFactory
    {
        return InvoiceFactory::new();
    }
}
