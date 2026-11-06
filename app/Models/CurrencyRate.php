<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CurrencyRate extends Model
{
    use SoftDeletes;
    protected $table = 'currency_rates';
    protected $hidden = ['id', 'deleted_at'];
}
