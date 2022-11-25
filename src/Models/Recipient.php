<?php

namespace ValeSaude\PaymentGatewayClient\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use ValeSaude\PaymentGatewayClient\Database\Factories\RecipientFactory;
use ValeSaude\PaymentGatewayClient\Models\Concerns\GeneratesUUIDOnInitializeTrait;
use ValeSaude\PaymentGatewayClient\Models\Concerns\HasGatewayIdTrait;
use ValeSaude\PaymentGatewayClient\Recipient\Enums\DocumentType;
use ValeSaude\PaymentGatewayClient\ValueObjects\Document;

/**
 * @property string       $name
 * @property Document     $document
 * @property string       $document_number
 * @property DocumentType $document_type
 */
class Recipient extends AbstractModel
{
    use GeneratesUUIDOnInitializeTrait;
    use HasFactory;
    use HasGatewayIdTrait;

    protected $table = 'payment_gateway_recipients';

    /**
     * @var array<string, class-string>
     */
    protected $casts = [
        'document' => Document::class,
        'document_type' => DocumentType::class,
    ];

    protected static function newFactory(): RecipientFactory
    {
        return RecipientFactory::new();
    }
}
