<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    //
    protected $guarded = [];
    protected $fillable = ['name', 'link_url', 'link_type', 'link_id', 'image', 'click_counts', 'created_at', 'updated_at', 'is_main_banner'];

    function event()
    {
        return $this->belongsTo(Event::class, 'link_id');
    }
}
