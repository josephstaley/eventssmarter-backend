<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Ticket extends Model
{
    use SoftDeletes;

    protected $guarded = [];
    protected $appends = ["origin_price", "ticket_prompts"];
    protected $hidden = ['eventWithTrashed'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('sort_priority', 'asc');
        });
    }

    function getAttributes()
    {
        $attributes = parent::getAttributes();
        unset($attributes['fee']);
        return $attributes;
    }

    function getAffiliateAmountAttribute()
    {
        return number_format(($this->attributes['affiliate_amount'] ?? 0) / 100, 2);
    }

    function formatPrice($price)
    {
        return '$' . number_format(intval($price) / 100, 2);
    }

    function getPriceAttribute()
    {
        if ($this->eventWithTrashed && $this->eventWithTrashed->organizationWithTrashed)
            $currency = Helpers::getCurrency($this->eventWithTrashed->organizationWithTrashed->currency);
        else
            $currency = Helpers::getCurrency('USD');
        $price = $this->getCentsAttribute();
        if ($currency['zero_decimal']) {
            $price = number_format($price, 2);
        } else
            $price = number_format($price / 100, 2);
        return $currency['symbol'] . ' ' . $price;
    }

    function getOriginPriceAttribute()
    {
        return $this->attributes['price'] / 100;
    }

    function getPriceInCents()
    {
        $cents = $this->attributes['price'] ?? 0;

        $stripe_fee = ($cents - StripeFees::$fixed) * StripeFees::$actual_percentage + StripeFees::$fixed;
        $michael_fee = ($cents - $stripe_fee) / (Stripe::$app_fee_percentage + 100) * Stripe::$app_fee_percentage;
        $this->fee = intval(ceil($michael_fee + $stripe_fee));
        return $cents;
    }

    function getVipPriceInCents()
    {
        $cents = $this->attributes['vip_ticket_price'] ?? 0;
        $stripe_fee = ($cents - StripeFees::$fixed) * StripeFees::$actual_percentage + StripeFees::$fixed;
        $michael_fee = ($cents - $stripe_fee) / (Stripe::$app_fee_percentage + 100) * Stripe::$app_fee_percentage;
        $this->fee = intval(ceil($michael_fee + $stripe_fee));
        return $cents;
    }

    function getUpgradePriceInCents()
    {

        $cents = $this->attributes['vip_ticket_price'] - $this->attributes['price'];
        $stripe_fee = ($cents - StripeFees::$fixed) * StripeFees::$actual_percentage + StripeFees::$fixed;
        $michael_fee = ($cents - $stripe_fee) / (Stripe::$app_fee_percentage + 100) * Stripe::$app_fee_percentage;
        $this->fee = intval(ceil($michael_fee + $stripe_fee));
        return $cents;
    }

    function getFee()
    {
        $this->getPriceInCents();

        return $this->fee;
    }

    function getVipFee()
    {
        $this->getVipPriceInCents();

        return $this->fee;
    }

    function getUpgradeFee()
    {
        $this->getUpgradePriceInCents();

        return $this->fee;
    }

    function getCentsAttribute()
    {
        return $this->getPriceInCents();
    }

    function getVipCentsAttribute()
    {
        return $this->getVipPriceInCents();
    }

    function event()
    {
        return $this->belongsTo(Event::class);
    }

    function eventWithTrashed()
    {
        return $this->belongsTo(Event::class, 'event_id')->withTrashed();
    }

    function bundles()
    {
        return $this->hasMany(TicketBundle::class);
    }

    function ticket_prompts()
    {
        return $this->hasOne(TicketPrompt::class);
    }

    function getTicketPromptsAttribute()
    {
        $prompts = $this->ticket_prompts()->first();
        if ($prompts){
            return $prompts->prompt_json;
        }
    }
}
