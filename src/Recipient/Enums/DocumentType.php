<?php

namespace ValeSaude\PaymentGatewayClient\Recipient\Enums;

use Spatie\Enum\Laravel\Enum;
use ValeSaude\PaymentGatewayClient\Concerns\ConvertsEnumValueToSlugTrait;

/**
 * @method static self CPF()
 * @method static self CNPJ()
 */
final class DocumentType extends Enum
{
    use ConvertsEnumValueToSlugTrait;
}
