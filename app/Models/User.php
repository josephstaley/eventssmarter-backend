<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Helper\Helpers as Helper;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'title', 'email', 'fname', 'lname', 'password', 'phone_number', 'avatar', 'facebook_link', 'twitter_link', 'linkedin_link', 'website_link', 'instagram_link', 'organization_enabled', 'device_token', 'referral_code'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'admin', 'stripe_id'
    ];

    private $following_cache = [];

    public static function isAdmin()
    {
        return ($user = auth()->user()) && $user->admin;
    }

    function organizations()
    {
        return $this->belongsToMany(Organization::class);
    }

    function getCustomersCount()
    {
        return count($this->hasMany(Customer::class)->get());
    }

    function follows()
    {
        return $this->belongsToMany(Organization::class, 'follow_user');
    }

    static function followHtml(Organization $organization)
    {
        $user = auth()->user();

        if (!$user)
            return '<div class="panel-heading">
                <a href="/decide"><i class="fa fa-heart-o"></i> Follow</a>
        </div>';

        $heart = 'fa-heart-o';
        $text = 'Follow';

        if ($user->following($organization)) {
            $heart = 'fa-heart';
            $text = 'Following';
        }

        return sprintf('<div class="panel-heading" data-follow="%s">
                <i class="fa %s"></i> <span class="text">%s</span>
        </div>', $organization->id, $heart, $text);
    }

    static function subscribed(Organization $organization)
    {
        $user = auth()->user();
        if ($user) {
            return Subscription::where('user_id', $user->id)->where('organization_id', $organization->id)->first();
        }
        return false;
    }

    function cancelSubscription($organization_id)
    {
        $user = auth()->user();
        $subscription = Subscription::where('user_id', $user->id)->where('organization_id', $organization_id)->first();
        if ($subscription) {
            Helper::cancelSubscriptionOnPaypal($subscription->subscription_id);
            $subscription->delete();
        }
    }

    function following(Organization $organization)
    {
        return isset($this->following_cache[$organization->id]) ? $this->following_cache[$organization->id] : ($this->following_cache[$organization->id] = $this->follows()->where('organization_id', $organization->id)->exists());
    }

    function vouchers()
    {
        return $this->hasMany(VoucherBundle::class);
    }

    function events()
    {
        return $this->belongsToMany(Event::class);
    }

    function streams()
    {
        return $this->hasMany(Stream::class);
    }

    function meetings()
    {
        return $this->hasMany(Meeting::class);
    }

    function getUpcomingEventsAttribute()
    {
        return $this->events()->upcoming('today')->until('tomorrow');
    }

    function getAvatarAttribute()
    {
        return $this->attributes['avatar'] ?? 'https://cdn0.iconfinder.com/data/icons/marketplace-filled-line/128/17_Costumer-128.png';
    }

    function getPaymentMethodsAttribute()
    {
        return (new Stripe)->getPaymentMethods($this);
    }
}
