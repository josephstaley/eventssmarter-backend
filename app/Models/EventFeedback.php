<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class EventFeedback extends Model
{
    protected $table = 'event_feedbacks';
    protected $fillable = ['event_id', 'user_id', 'rating', 'review'];

    function user()
    {
        return $this->belongsTo(User::class);
    }

}
