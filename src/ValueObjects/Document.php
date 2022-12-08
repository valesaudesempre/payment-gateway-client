<?php

namespace ValeSaude\PaymentGatewayClient\ValueObjects;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;
use ValeSaude\PaymentGatewayClient\Casts\DocumentCast;
use ValeSaude\PaymentGatewayClient\Enums\DocumentType;
use ValeSaude\PaymentGatewayClient\Validators\CNPJValidator;
use ValeSaude\PaymentGatewayClient\Validators\Contracts\ValidatorInterface;
use ValeSaude\PaymentGatewayClient\Validators\CPFValidator;

class Document extends AbstractValueObject implements Castable
{
    private string $number;
    private DocumentType $type;

    public function __construct(string $number, DocumentType $type)
    {
        $validator = $this->resolveValidator($type);

        if (!$validator->validate($number)) {
            throw new InvalidArgumentException("The provided value is not a valid {$type->label}.");
        }

        $this->number = $validator->sanitize($number);
        $this->type = $type;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function getType(): DocumentType
    {
        return $this->type;
    }

    private function resolveValidator(DocumentType $type): ValidatorInterface
    {
        if ($type->equals(DocumentType::CNPJ())) {
            return resolve(CNPJValidator::class);
        }

        return resolve(CPFValidator::class);
    }

    /**
     * @param array<array-key, mixed> $arguments
     */
    public static function castUsing(array $arguments): CastsAttributes
    {
        return new DocumentCast();
    }
}
