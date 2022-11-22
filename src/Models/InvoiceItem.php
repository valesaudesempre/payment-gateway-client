<?php

namespace ValeSaude\PaymentGatewayClient\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ValeSaude\PaymentGatewayClient\Database\Factories\InvoiceItemFactory;
use ValeSaude\PaymentGatewayClient\Models\Concerns\GeneratesUUIDOnInitializeTrait;
use ValeSaude\PaymentGatewayClient\ValueObjects\Money;

/**
 * @property Money       $price
 * @property int         $quantity
 * @property string      $description
 * @property string|null $gateway_id
 * @property string      $gateway_slug
 */
class InvoiceItem extends AbstractModel
{
    use GeneratesUUIDOnInitializeTrait;
    use HasFactory;

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
