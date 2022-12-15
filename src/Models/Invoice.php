<?php

namespace ValeSaude\PaymentGatewayClient\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use ValeSaude\PaymentGatewayClient\Casts\InvoicePaymentMethodCollectionCast;
use ValeSaude\PaymentGatewayClient\Casts\InvoiceSplitRuleCollectionCast;
use ValeSaude\PaymentGatewayClient\Collections\InvoiceSplitRuleCollection;
use ValeSaude\PaymentGatewayClient\Database\Factories\InvoiceFactory;
use ValeSaude\PaymentGatewayClient\Invoice\Collections\InvoicePaymentMethodCollection;
use ValeSaude\PaymentGatewayClient\Invoice\Enums\InvoicePaymentMethod;
use ValeSaude\PaymentGatewayClient\Invoice\Enums\InvoiceStatus;
use ValeSaude\PaymentGatewayClient\Invoice\InvoiceDTO;
use ValeSaude\PaymentGatewayClient\Models\Concerns\GeneratesUUIDOnInitializeTrait;
use ValeSaude\PaymentGatewayClient\Models\Concerns\HasGatewayIdTrait;
use ValeSaude\PaymentGatewayClient\Models\Concerns\HasOwnerTrait;
use ValeSaude\PaymentGatewayClient\QueryBuilders\InvoiceQueryBuilder;
use ValeSaude\PaymentGatewayClient\ValueObjects\Money;

/**
 * @property string|null                    $url
 * @property CarbonImmutable                $due_date
 * @property InvoicePaymentMethodCollection $available_payment_methods
 * @property InvoiceSplitRuleCollection     $splits
 * @property int                            $max_installments
 * @property InvoiceStatus                  $status
 * @property Money                          $total
 * @property CarbonImmutable|null           $paid_at
 * @property CarbonImmutable|null           $canceled_at
 * @property CarbonImmutable|null           $refunded_at
 * @property Money|null                     $refunded_amount
 * @property int|null                       $installments
 * @property string|null                    $bank_slip_code
 * @property string|null                    $pix_code
 *
 * @method static InvoiceQueryBuilder query()
 */
class Invoice extends AbstractModel
{
    use GeneratesUUIDOnInitializeTrait;
    use HasFactory;
    use HasGatewayIdTrait;
    use HasOwnerTrait;
    use SoftDeletes;

    protected $table = 'payment_gateway_invoices';

    /**
     * @var array<string, class-string|string>
     */
    protected $casts = [
        'due_date' => 'immutable_date',
        'max_installments' => 'int',
        'status' => InvoiceStatus::class,
        'available_payment_methods' => InvoicePaymentMethodCollectionCast::class,
        'splits' => InvoiceSplitRuleCollectionCast::class,
        'paid_at' => 'immutable_datetime',
        'canceled_at' => 'immutable_datetime',
        'refunded_at' => 'immutable_datetime',
        'installments' => 'int',
        'refunded_amount' => Money::class,
    ];

    public function getTotalAttribute(): Money
    {
        $total = $this->items->sum(
            fn (InvoiceItem $item) => $item->price->multiply($item->quantity)->getCents()
        );

        return new Money($total);
    }

    /**
     * @param Builder $query
     */
    public function newEloquentBuilder($query): InvoiceQueryBuilder
    {
        return new InvoiceQueryBuilder($query);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function markAsPaid(?CarbonImmutable $paidAt = null): void
    {
        $this->status = InvoiceStatus::PAID();
        $this->paid_at = $paidAt ?? CarbonImmutable::now();
        $this->save();
    }

    public function markAsCanceled(): void
    {
        $this->status = InvoiceStatus::CANCELED();
        $this->canceled_at = CarbonImmutable::now();
        $this->save();
    }

    public function markAsRefunded(Money $refundedAmount): void
    {
        $this->status = InvoiceStatus::REFUNDED();
        $this->refunded_at = CarbonImmutable::now();
        $this->refunded_amount = $refundedAmount;
        $this->save();
    }

    public function setStatus(InvoiceStatus $status): void
    {
        $this->update(compact('status'));
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
