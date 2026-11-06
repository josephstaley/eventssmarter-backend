<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use RolfHaug\TokenAuth\LoginToken;
use Illuminate\Support\Facades\Crypt;

class ReservationTicketsAvailable extends Notification
{
    use Queueable;
    public $user;
    public $organization;
    private $voucher_bundle;

    public function __construct($user, $voucher_bundle)
    {
        $this->user = $user;
        $this->organization = $voucher_bundle->ticket->event->organization;
        $this->voucher_bundle = $voucher_bundle;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $options = [];
        $reservation_start_time = date_create($this->voucher_bundle->reservation_start_time);
        $options['reservation_date'] = date_format($reservation_start_time, "l, M d");
        $reservation_time = $this->voucher_bundle->ticket->reservation_time;
        if ($reservation_time === "01:00:00"){
            $options['reservation_time'] = date_format($reservation_start_time, "h:i A").' - '.date_format(date_add($reservation_start_time, date_interval_create_from_date_string("1 hours")), "h:i A");
        }
        else {
            $reservation_time = (int)substr($reservation_time, 3, 2);
            $options['reservation_time'] = date_format($reservation_start_time, "h:i A").' - '.date_format(date_add($reservation_start_time, date_interval_create_from_date_string($reservation_time." minutes")), "h:i A");
        }
        if (isset($this->organization->ad_link)){
            $options['ad_link'] = url($this->organization->ad_link);
        }
        else {
            $options['ad_link'] = url('https://athleticjunction.com/store/');
        }

        return (new MailMessage)->markdown('mail.tickets.reservation', $options);
    }

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
