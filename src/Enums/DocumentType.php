<?php

namespace ValeSaude\PaymentGatewayClient\Enums;

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
