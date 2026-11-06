<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventMember extends Model
{
    use SoftDeletes;

    protected $table = 'event_members';
    protected $fillable = ['event_id', 'invited_user_email', 'invited_user_id', 'user_role', 'invitation_accept_status'];

    function user()
    {
        return $this->belongsTo(User::class, 'invited_user_email', 'email');
    }

    function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }
}
