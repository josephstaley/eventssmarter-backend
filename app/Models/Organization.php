<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use SoftDeletes;
    protected $guarded = [];

    protected $hidden = ['password', 'stripe_account'];

    function locationString()
    {
        return $this->address;
    }

    function getRouteKeyName()
    {
        return 'slug';
    }

    function events()
    {
        return $this->hasMany(Event::class);
    }

    function getUpcomingEventsAttribute()
    {
        return $this->events()->upcoming('today')->until('tomorrow');
    }

    function getAdLinkAttribute()
    {
        return $this->attributes['ad_link'] ?? 'https://www.athleticjunction.com/store/';
    }

    function getAdImageAttribute()
    {
        return $this->attributes['ad_image'] ?? 'https://www.eventssmarter.com/images/branded.png';
    }

    function getCityStateAttribute()
    {
        if ($this->city && $this->state)
            return "{$this->city}, {$this->state}";

        if ($this->state)
            return $this->state;

        return false;
    }

    function users()
    {
        return $this->belongsToMany(User::class);
    }

    static function categories()
    {
        return Organization::groupBy('category')->limit(100)->get()->pluck('category');
    }

    function monthly_amount()
    {
        return $this->monthly_subscription_amount;
    }

    function yearly_amount()
    {
        return $this->yearly_subscription_amount;
    }

    function getCurrencySymbolAttribute()
    {
        return Helpers::getCurrency($this->currency)['symbol'];
    }

    function promo_codes()
    {
        return $this->hasMany(PromoCode::class);
    }

    function banners()
    {
        return $this->hasMany(Banner::class, 'link_id');
    }
}
