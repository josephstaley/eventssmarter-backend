<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Theatre extends Model
{
    use SoftDeletes;
    protected $table = 'theatres';
    protected $casts = [
        'seat_json' => 'array',
    ];
}
