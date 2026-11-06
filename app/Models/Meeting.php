<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Meeting extends Model
{
    use SoftDeletes;
    protected $table = 'meetings';

    function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
