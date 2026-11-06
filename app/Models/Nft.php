<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Nft extends Model
{
    use SoftDeletes;

    protected $fillable = ['token_id', 'name', 'description', 'image_url'];

    protected $hidden = ['id', 'deleted_at'];

}
