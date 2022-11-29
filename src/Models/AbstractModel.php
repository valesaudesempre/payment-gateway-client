<?php

namespace ValeSaude\PaymentGatewayClient\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 */
abstract class AbstractModel extends Model
{
    public $incrementing = false;
    protected $guarded = [];
    protected $keyType = 'string';
}
