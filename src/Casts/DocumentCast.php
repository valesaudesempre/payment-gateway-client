<?php

namespace ValeSaude\PaymentGatewayClient\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;
use ValeSaude\PaymentGatewayClient\Enums\DocumentType;
use ValeSaude\PaymentGatewayClient\ValueObjects\Document;

class DocumentCast implements CastsAttributes
{
    /**
     * @param string                                                $value
     * @param array{document_type: string, document_number: string} $attributes
     */
    public function get($model, string $key, $value, array $attributes): Document
    {
        return new Document(
            $attributes['document_number'],
            DocumentType::from($attributes['document_type'])
        );
    }

    /**
     * @param Document             $value
     * @param array<string, mixed> $attributes
     *
     * @return array{document_type: string, document_number: string}
     */
    public function set($model, string $key, $value, array $attributes): array
    {
        if (!$value instanceof Document) {
            throw new InvalidArgumentException('The given value is not a Document instance.');
        }

        return [
            'document_number' => $value->getNumber(),
            'document_type' => $value->getType(),
        ];
    }
}
