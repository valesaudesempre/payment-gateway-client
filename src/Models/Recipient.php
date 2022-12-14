<?php

namespace ValeSaude\PaymentGatewayClient\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use ValeSaude\PaymentGatewayClient\Database\Factories\RecipientFactory;
use ValeSaude\PaymentGatewayClient\Enums\DocumentType;
use ValeSaude\PaymentGatewayClient\Models\Concerns\GeneratesUUIDOnInitializeTrait;
use ValeSaude\PaymentGatewayClient\Models\Concerns\HasGatewayIdTrait;
use ValeSaude\PaymentGatewayClient\Models\Concerns\HasOwnerTrait;
use ValeSaude\PaymentGatewayClient\Recipient\Enums\RecipientStatus;
use ValeSaude\PaymentGatewayClient\Recipient\RecipientDTO;
use ValeSaude\PaymentGatewayClient\ValueObjects\Address;
use ValeSaude\PaymentGatewayClient\ValueObjects\BankAccount;
use ValeSaude\PaymentGatewayClient\ValueObjects\Document;
use ValeSaude\PaymentGatewayClient\ValueObjects\JsonObject;
use ValeSaude\PaymentGatewayClient\ValueObjects\Phone;

/**
 * @property string            $name
 * @property Document          $document
 * @property string            $document_number
 * @property DocumentType      $document_type
 * @property string|null       $representative_name
 * @property Document|null     $representative_document
 * @property string|null       $representative_document_number
 * @property DocumentType|null $representative_document_type
 * @property Address           $address
 * @property Phone             $phone
 * @property BankAccount       $bank_account
 * @property bool              $automatic_withdrawal
 * @property RecipientStatus   $status
 * @property JsonObject        $gateway_specific_data
 */
class Recipient extends AbstractModel
{
    use GeneratesUUIDOnInitializeTrait;
    use HasFactory;
    use HasGatewayIdTrait;
    use HasOwnerTrait;
    use SoftDeletes;

    protected $table = 'payment_gateway_recipients';

    /**
     * @var array<string, class-string|string>
     */
    protected $casts = [
        'document' => Document::class,
        'document_type' => DocumentType::class,
        'representative_document' => Document::class,
        'representative_document_type' => DocumentType::class.':nullable',
        'address' => Address::class,
        'phone' => Phone::class,
        'bank_account' => BankAccount::class,
        'automatic_withdrawal' => 'bool',
        'status' => RecipientStatus::class,
        'gateway_specific_data' => JsonObject::class,
    ];

    public function markAsApproved(): void
    {
        $this->update(['status' => RecipientStatus::APPROVED()]);
    }

    public function markAsDeclined(): void
    {
        $this->update(['status' => RecipientStatus::DECLINED()]);
    }

    public static function fromRecipientDTO(RecipientDTO $data): self
    {
        return new self([
            'name' => $data->name,
            'document' => $data->document,
            'representative_name' => optional($data->representative)->name,
            'representative_document' => optional($data->representative)->document,
            'address' => $data->address,
            'phone' => $data->phone,
            'bank_account' => $data->bankAccount,
            'automatic_withdrawal' => $data->automaticWithdrawal,
            'gateway_specific_data' => $data->gatewaySpecificData,
        ]);
    }

    protected static function newFactory(): RecipientFactory
    {
        return RecipientFactory::new();
    }
}
