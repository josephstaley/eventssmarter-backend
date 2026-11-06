<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class EventAdvertise extends Notification
{
    use Queueable;
    public $organization;
    public $event;
    public $promo_code;

    public function __construct($organization, $event, $promo_code)
    {
        $this->organization = $organization;
        $this->event = $event;
        $this->promo_code = $promo_code;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('Advertise '.$this->event->name.' Event')
                    ->line('Event Address: https://www.eventssmarter.com/'.$this->organization->slug.'/'.$this->event->slug.'_'.$this->event->id.'?promo_code='.$this->promo_code)
                    ->line('Thank you for using '.env('APP_NAME'));
    }

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
