<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketBundle extends Model
{
    use SoftDeletes;
    protected $table = 'ticket_bundles';
    protected $guarded = [];
    protected $appends = ["origin_price"];

    function getPriceAttribute()
    {
        // subscription discount logic
        // if (auth()->user() && $this->ticket->event && User::subscribed($this->ticket->event->organization) && $this->ticket->discount_percent > 0) {
        //     return $this->attributes['price'] * (100 - $this->ticket->discount_percent) * 0.01;
        // } else
        //     return $this->attributes['price'];

        if ($this->ticket->eventWithTrashed && $this->ticket->eventWithTrashed->organizationWithTrashed)
            $currency = Helpers::getCurrency($this->ticket->eventWithTrashed->organizationWithTrashed->currency);
        else
            $currency = Helpers::getCurrency('USD');

        return $currency['symbol'] . ' ' . number_format($this->attributes['price'] * 0.01, decimals: 2);
    }

    function getOriginPriceAttribute()
    {
        return $this->attributes['price'] * 0.01;
    }

    function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}
