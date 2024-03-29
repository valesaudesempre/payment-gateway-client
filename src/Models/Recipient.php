<?php

namespace ValeSaude\PaymentGatewayClient\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use ValeSaude\LaravelValueObjects\Address;
use ValeSaude\LaravelValueObjects\BankAccount;
use ValeSaude\LaravelValueObjects\Document;
use ValeSaude\LaravelValueObjects\Enums\DocumentType;
use ValeSaude\LaravelValueObjects\JsonObject;
use ValeSaude\LaravelValueObjects\Phone;
use ValeSaude\PaymentGatewayClient\Database\Factories\RecipientFactory;
use ValeSaude\PaymentGatewayClient\Models\Concerns\GeneratesUUIDOnInitializeTrait;
use ValeSaude\PaymentGatewayClient\Models\Concerns\HasGatewayIdTrait;
use ValeSaude\PaymentGatewayClient\Models\Concerns\HasOwnerTrait;
use ValeSaude\PaymentGatewayClient\QueryBuilders\RecipientQueryBuilder;
use ValeSaude\PaymentGatewayClient\Recipient\Enums\RecipientStatus;
use ValeSaude\PaymentGatewayClient\Recipient\RecipientDTO;
use ValeSaude\PaymentGatewayClient\Recipient\RepresentativeDTO;

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

    public function newEloquentBuilder($query): RecipientQueryBuilder
    {
        return new RecipientQueryBuilder($query);
    }

    public function markAsApproved(): void
    {
        $this->update(['status' => RecipientStatus::APPROVED()]);
    }

    public function markAsDeclined(): void
    {
        $this->update(['status' => RecipientStatus::DECLINED()]);
    }

    public function toRecipientDTO(): RecipientDTO
    {
        return new RecipientDTO(
            $this->name,
            $this->document,
            $this->address,
            $this->phone,
            $this->bank_account,
            $this->automatic_withdrawal,
            $this->gateway_specific_data,
            $this->representative_name && $this->representative_document
                ? new RepresentativeDTO($this->representative_name, $this->representative_document)
                : null
        );
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
