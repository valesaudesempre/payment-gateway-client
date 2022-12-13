<?php

namespace ValeSaude\PaymentGatewayClient\Models;

use Illuminate\Database\Eloquent\Model;
use ValeSaude\PaymentGatewayClient\Models\Concerns\HasDefaultOrderingTrait;

/**
 * @property string $id
 */
abstract class AbstractModel extends Model
{
    use HasDefaultOrderingTrait;

    public $incrementing = false;
    protected $guarded = [];
    protected $keyType = 'string';
}
