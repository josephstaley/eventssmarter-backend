<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SubscribeCancelled extends Notification
{
    use Queueable;
    public $organization;
    public $promo_code;

    public function __construct($organization)
    {
        $this->organization = $organization;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $organization_url = env('FRONTEND_URL') . '/organization/' . $this->organization->slug;
        return (new MailMessage)
            ->line('You unsubscribed from ' . $this->organization->name . '.')
            ->action('Resubscribe', $organization_url)
            ->line('Thank you for using ' . env('APP_NAME'));
    }

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
