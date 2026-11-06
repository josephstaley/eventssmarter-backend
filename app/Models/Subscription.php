<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'organization_id', 'subscription_id'];

    function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
