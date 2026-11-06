<?php

namespace App\Models;

use \QrCode;
use Carbon\Carbon;
use App\Notifications\TicketsAvailable;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = ['ticket_id', 'code', 'downloaded_at', 'used_at', 'refunded_at'];

    protected $casts = [
        'downloaded_at' => 'datetime',
        'used_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    private $times = 1;

    public function createTickets(User $user, Ticket $ticket, $bundle, User $emailUser, $charge_id, $actualAmount, $affiliate_id, $affiliateAmount, $ticket_type)
    {
        $actualAmount = $actualAmount / $this->times;
        $affiliateAmount = $affiliateAmount / $this->times;

        $ticket->quantity = $ticket->quantity - $bundle['count'] * $this->times;
        $ticket->save();

        foreach (range(1, $this->times) as $n) {
            $voucher = self::forceCreate([
                'user_id' => $user->id,
                'ticket_id' => $ticket->id,
                'ticket_bundle_id' => $bundle['id'],
                'charge_id' => $charge_id,
                'detail_amount' => $actualAmount,
                'detail_text' => ($bundle['ticket_type'] == 1 ? 'Normal ' : 'VIP ') . "Ticket $n of {$this->times} : " . $bundle['count'] . "-Bundle",
                'affiliate_id' => $affiliate_id,
                'affiliate_amount' => $affiliateAmount,
                'ticket_type' => $ticket_type
            ]);
            $voucher->generateCode();
        }

        // $emailUser->notify(new TicketsAvailable($this->times));
    }


    public static function for ($quantity)
    {
        $voucher = new self;

        $voucher->times = $quantity;

        return $voucher;
    }

    public function getQrCodeAttribute()
    {
        return QrCode::format('png')->size(250)->generate($this->code);
    }

    public function scopeGenerateCode()
    {
        $this->update(['code' => encrypt(
                implode('|', [
                        $this->ticket_id,
                        $this->id,
                        base64_encode($this->created_at),
                        uniqid('aje' . rand(10, 99), true)
                    ]
                ))]
        );

        return $this;
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function ticket_bundle()
    {
        return $this->belongsTo(TicketBundle::class);
    }

    public function voucher_bundle()
    {
        return $this->belongsTo(VoucherBundle::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function refund()
    {
        if($this->affiliate_id && $this->affiliate_amount) {
            Stripe::refund($this->affiliate_id, $this->affiliate_amount);
        }

        if($this->detail_amount) {
            $refund = Stripe::refund($this->charge_id, $this->detail_amount);

            $this->update(['refunded_at'=>new Carbon()]);

            return $refund;
        }
	    return null;
    }
}
