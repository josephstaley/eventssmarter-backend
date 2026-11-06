<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CallTicketsAvailable extends Notification
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
        $options['room_link'] = 'https://meet.eventssmarter.com/'.$this->voucher_bundle->ticket->stream_url;
        $call_start_time = date_create($this->voucher_bundle->call_start_time);
        $options['call_date'] = date_format($call_start_time, "l, M d");
        $call_time = $this->voucher_bundle->ticket->call_time;
        if ($call_time === "01:00:00"){
            $options['call_time'] = date_format($call_start_time, "h:i A").' - '.date_format(date_add($call_start_time, date_interval_create_from_date_string("1 hours")), "h:i A");
        }
        else {
            $call_time = (int)substr($call_time, 3, 2);
            $options['call_time'] = date_format($call_start_time, "h:i A").' - '.date_format(date_add($call_start_time, date_interval_create_from_date_string($call_time." minutes")), "h:i A");
        }
        $options['invite_link'] = 'https://meet.eventssmarter.com/'.$this->voucher_bundle->ticket->attendee_url;
        if (isset($this->organization->ad_link)){
            $options['ad_link'] = url($this->organization->ad_link);
        }
        else {
            $options['ad_link'] = url('https://athleticjunction.com/store/');
        }

        return (new MailMessage)->markdown('mail.tickets.call', $options);
    }

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
