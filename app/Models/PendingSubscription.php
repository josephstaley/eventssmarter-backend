<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PendingSubscription extends Model
{
    use SoftDeletes;
    protected $guarded = [];

    protected $hidden = ['deleted_at'];
}
