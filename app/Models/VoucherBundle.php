<?php

namespace App\Models;

use \QrCode;
use Carbon\Carbon;
use App\Notifications\TicketsAvailable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Notifications\CallTicketsAvailable;
use App\Notifications\ReservationTicketsAvailable;

class VoucherBundle extends Model
{
    use SoftDeletes;
    protected $fillable = ['ticket_id', 'code', 'downloaded_at', 'used_at', 'refunded_at'];

    protected $casts = [
        'downloaded_at' => 'datetime',
        'used_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    private $times = 1;

    public function createTickets(User $user, Ticket $ticket, $bundle, User $emailUser, $charge_id, $actualAmount, $affiliate_id, $affiliateAmount, $call_start_date, $reservation_start_date, $selectedSeats)
    {
        //$actualAmount = $actualAmount / $this->times;
        //$affiliateAmount = $affiliateAmount / $this->times;

        $ticket->quantity = $ticket->quantity - $bundle['count'] * $this->times;
        $ticket->save();
        $current_voucher_count = 0;
        $theatre_json = $ticket->event->theatre_json;
        foreach (range(1, $this->times) as $n) {
            $voucher_bundle = self::forceCreate([
                'user_id' => $user->id,
                'ticket_id' => $ticket->id,
                'ticket_bundle_id' => $bundle['id'],
                'charge_id' => $charge_id,
                'detail_amount' => $actualAmount,
                'detail_text' => "Normal Ticket $n of {$this->times} : " . $bundle['count'] . "-Bundle",
                // 'affiliate_id' => $affiliate_id,
                'affiliate_amount' => $affiliateAmount,
                'ticket_type' => $bundle['ticket_type'],
            ]);
            if ($ticket->is_call){
                $voucher_bundle->call_start_time = $call_start_date;
                $voucher_bundle->save();
            }
            if ($ticket->is_reservation){
                $voucher_bundle->reservation_start_time = $reservation_start_date;
                $voucher_bundle->save();
            }
            if ($ticket->event->theatre_id && $ticket->event->theatre_json && $selectedSeats && count($selectedSeats) > 0){
                $seat_json = [];
                foreach (range(0, $bundle['count'] - 1) as $index) {
                    array_push($seat_json, $selectedSeats[$index + $current_voucher_count]);
                }
                $voucher_bundle->seat_json = json_encode($seat_json);
                foreach($seat_json as $seat){
                    $_seat = $theatre_json['colAreas']['objArea'][$seat->AreaNum - 1]['objRow'][$seat->GridRowId - 1]['objSeat'][$seat->seatNumber - 1];
                    $_seat['SeatStatus'] = "1";
                    $_seat['selled_at'] = Carbon::now();
                    $_seat['user_id'] = $voucher_bundle->user()->first()->id;
                    $theatre_json['colAreas']['objArea'][$seat->AreaNum - 1]['objRow'][$seat->GridRowId - 1]['objSeat'][$seat->seatNumber - 1] = $_seat;
                }
            }
            $voucher_bundle->generateCode();
            $current_voucher_count += $bundle['count'];
        }
        if ($ticket->event->theatre_id && $ticket->event->theatre_json && $selectedSeats && count($selectedSeats) > 0){
            $ticket->event->theatre_json = $theatre_json;
            $ticket->event->save();
        }
        if ($ticket->is_call){
            $emailUser->notify(new CallTicketsAvailable($emailUser, $voucher_bundle));
            $ticket->event->organization->users()->first()->notify(new CallTicketsAvailable($ticket->event->organization->users()->first(), $voucher_bundle));
        }
        else if ($ticket->is_reservation){
            $emailUser->notify(new ReservationTicketsAvailable($emailUser, $voucher_bundle));
            $ticket->event->organization->users()->first()->notify(new ReservationTicketsAvailable($ticket->event->organization->users()->first(), $voucher_bundle));
        }
        else $emailUser->notify(new TicketsAvailable($emailUser, $voucher_bundle->ticket->event->organization));
        return $voucher_bundle;
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
        return $this->belongsTo(Ticket::class)->withTrashed();
    }

    public function ticket_bundle()
    {
        return $this->belongsTo(TicketBundle::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function refund()
    {
        if($this->detail_amount) {
            if ($this->payment_method === 'stripe'){
                $fee = intval(ceil($this->detail_amount * (0.029 + $this->ticket->event->organization->app_fee_percentage * 0.01) + 40));
                $refund = Stripe::refund($this->charge_id, $this->detail_amount - $fee);

                $this->update(['refunded_at'=>new Carbon()]);

                return $refund;
            }
            // else if ($this->payment_method === 'paypal'){
            //     $order = Helper::refundOrder($this->charge_id, 5);
            //     dd($order);
            // }
        }
	    return null;
    }

    function vouchers()
    {
        return $this->hasMany(Voucher::class);
    }

    function getSeatAttribute(){
        return \json_decode($this->seat_json);
    }
}
