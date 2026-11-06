<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use DateTimeInterface;

class Event extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'theatre_json' => 'array',
    ];

    protected $hidden = [
        'investors',
        'total_investment',
        'expenses',
        'sponsorship_sales'
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    function locationString()
    {
        return $this->address;
    }

    function formatPrice($price)
    {
        return '$' . number_format(intval($price) / 100, 2);
    }

    function link($name, $extra = [], $event_slug = '', $event_id = '')
    {
        $slug = $event_slug == '' ? $this->slug : $event_slug;
        $event_id = $event_id == '' ? $this : $event_id;
        return route($name, [$this->organization, $slug, $event_id] + $extra);
    }

    function scopeUpcoming($query, $dateTime = null)
    {
        if (!$dateTime instanceof \DateTime) {
            $dateTime = Carbon::parse($dateTime);
        }

        return $query->orderBy('ends_at', 'ASC')->where('ends_at', '>', $dateTime);
    }

    function scopeUntil($query, $dateTime = null)
    {
        if (!$dateTime instanceof \DateTime) {
            $dateTime = Carbon::parse($dateTime);
        }

        return $query
            ->where('starts_at', '<', $dateTime)
            ->orWhere('ends_at', '<', $dateTime);
    }

    function scopeNotended($query, $dateTime = null)
    {
        if (!$dateTime instanceof \DateTime) {
            $dateTime = Carbon::parse($dateTime);
        }

        return $query->orderBy('starts_at', 'ASC')->where('ends_at', '>', $dateTime);
    }

    function canEdit()
    {
        return Helpers::can('update', $this);
    }

    function getPriceAttribute()
    {
        $sortedTickets = $this->tickets->sortBy('cents')->pluck('cents');

        if ($sortedTickets->isEmpty()) {
            return 'TBD';
        }

        if ($sortedTickets->first() === $sortedTickets->last()) {
            return $this->formatPrice($sortedTickets->first());
        }

        $sortedTickets = $sortedTickets->map([$this, 'formatPrice']);

        return $sortedTickets->first() . ' - ' . $sortedTickets->last();
    }

    function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    function organizationWithTrashed()
    {
        return $this->belongsTo(Organization::class, 'organization_id')->withTrashed();
    }

    function banners()
    {
        return $this->hasMany(Banner::class, 'link_id');
    }

    function attachments()
    {
        return $this->hasMany(EventAttachment::class);
    }

    function sales()
    {
        $this->sales_query = DB::table('voucher_bundles')
            ->leftJoin('users', 'voucher_bundles.user_id', '=', 'users.id')
            ->leftJoin('promo_codes', 'voucher_bundles.promo_code_org_id', '=', 'promo_codes.id')
            ->where('voucher_bundles.event_id', $this->id)
            ->whereNull('voucher_bundles.deleted_at')
            ->select('voucher_bundles.*', 'users.*', 'promo_codes.*', 'voucher_bundles.detail_text as ticket_name', 'voucher_bundles.id as voucher_id', 'voucher_bundles.affiliate_amount as sent_affiliate_amount', 'voucher_bundles.created_at as purchased_date')
            ->orderBy('voucher_bundles.created_at', 'desc');
        if (!auth()->user()->admin) {
            $this->sales_query->where(function ($q) {
                $q->where('voucher_bundles.is_hide_on_sales', '!=', 1)->orWhereNull('voucher_bundles.is_hide_on_sales');
            })->where('voucher_bundles.created_at', '<', Carbon::now()->subHours($this->organization->sales_time_delay)->toDateTimeString());
        }
        $sold = intval(with(clone $this->sales_query)->where('voucher_bundles.refunded_at', '=', null)->sum('voucher_bundles.detail_amount'));
        $currency = Helpers::getCurrency($this->organization->currency);
        $refund_total = intval(with(clone $this->sales_query)->where('voucher_bundles.refunded_at', '!=', null)->sum('voucher_bundles.detail_amount'));
        if (!$currency['zero_decimal']) {
            $sold /= 100;
            $refund_total = Helpers::getSymbol($this->organization->currency) . ' ' . number_format($refund_total / 100, 2);
        } else {
            $refund_total = Helpers::getSymbol($this->organization->currency) . ' ' . number_format($refund_total, 2);
        }

        $tickets = VoucherBundle::where('event_id', $this->id);
        if (!auth()->user()->admin) {
            $tickets = $tickets->where(function ($q) {
                $q->where('voucher_bundles.is_hide_on_sales', '!=', 1)->orWhereNull('voucher_bundles.is_hide_on_sales');
            })->where('voucher_bundles.created_at', '<', Carbon::now()->subHours($this->organization->sales_time_delay)->toDateTimeString());
        }
        $tickets = $tickets->count();

        return $this->sales_info ?? $this->sales_info = (object) [
            'tickets' => $tickets,
            'sold' => Helpers::getSymbol($this->organization->currency) . ' ' . number_format($sold, 2),
            'sold_amount' => $sold,
            'attendees' => VoucherBundle::where('event_id', $this->id)->where(function ($q) {
                $q->where('is_hide_on_sales', '!=', 1)->orWhereNull('is_hide_on_sales');
            })->where('used_at', '!=', null)->count(),
            'refunds' => VoucherBundle::where('event_id', $this->id)->where(function ($q) {
                $q->where('is_hide_on_sales', '!=', 1)->orWhereNull('is_hide_on_sales');
            })->where('refunded_at', '!=', null)->count(),
            'refunds_total' => $refund_total,
            'query' => $this->sales_query
        ];
    }

    function getImageAttribute()
    {
        return $this->attributes['image'] && $this->attributes['image'] !== '' ? $this->attributes['image'] : 'https://cdn0.iconfinder.com/data/icons/marketplace-filled-line/128/09_Quality-128.png';
    }

    function getStreetNumberAttribute()
    {
        return $this->attributes['street_number'] ?? !$this->organization ? '' : $this->organization->street_number;
    }

    function getStreetAttribute()
    {
        return $this->attributes['street'] ?? !$this->organization ? '' : $this->organization->street;
    }

    function getCityAttribute()
    {
        return $this->attributes['city'] ?? (!$this->organization) ? '' : $this->organization->city;
    }

    function getStateAttribute()
    {
        return $this->attributes['state'] ?? !$this->organization ? '' : $this->organization->state;
    }

    function getZipAttribute()
    {
        return $this->attributes['zip'] ?? !$this->organization ? '' : $this->organization->zip;
    }

    function getAddressLineAttribute()
    {
        if ($this->street) {
            return collect(['street_number', 'street', 'city', 'state', 'zip'])->map(function ($attr) {
                return $this->$attr;
            })->filter()->implode(' ');
        } else {
            return $this->organization ? $this->organization->locationString() : '';
        }
    }

    function mapLink()
    {
        return "https://www.google.com/maps/dir/" . urlencode($this->address);
    }

    static function cities()
    {
        return Event::groupBy('city')->limit(100)->orderBy('city')->get()->pluck('city')->filter();
    }

    function vouchers()
    {
        return $this->hasManyThrough('App\Models\VoucherBundle', 'App\Models\Ticket');
    }

    function getCityStateAttribute()
    {
        if ($this->city && $this->state)
            return "{$this->city}, {$this->state}";

        if ($this->state)
            return $this->state;

        return !$this->organization ? '' : $this->organization->cityState;
    }

    function theatre()
    {
        return $this->BelongsTo(Theatre::class);
    }

    function getInvestorsAttribute($value)
    {
        return json_decode($value);
    }

    public function makeFinancialInfoVisible()
    {
        return $this->makeVisible([
            'investors',
            'total_investment',
            'expenses',
            'sponsorship_sales'
        ]);
    }
}
