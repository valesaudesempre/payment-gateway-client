<?php

namespace ValeSaude\PaymentGatewayClient\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use ValeSaude\LaravelValueObjects\Money;
use ValeSaude\PaymentGatewayClient\Database\Factories\InvoiceItemFactory;
use ValeSaude\PaymentGatewayClient\Models\Concerns\GeneratesUUIDOnInitializeTrait;
use ValeSaude\PaymentGatewayClient\Models\Concerns\HasGatewayIdTrait;

/**
 * @property Money  $price
 * @property int    $quantity
 * @property string $description
 */
class InvoiceItem extends AbstractModel
{
    use GeneratesUUIDOnInitializeTrait;
    use HasFactory;
    use HasGatewayIdTrait;
    use SoftDeletes;

    protected $table = 'payment_gateway_invoice_items';

    /**
     * @var array<string, class-string|string>
     */
    protected $casts = [
        'quantity' => 'int',
        'price' => Money::class,
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public static function newFactory(): InvoiceItemFactory
    {
        return InvoiceItemFactory::new();
    }
}
