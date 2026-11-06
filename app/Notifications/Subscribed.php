<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class Subscribed extends Notification
{
    use Queueable;
    public $organization;

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
            ->line('You subscribed successfully to ' . $this->organization->name . '.')
            ->line('You can get free tickets for upcoming events of this organization.')
            ->action('Check Events', $organization_url)
            ->line('Thank you for using ' . env('APP_NAME'));
    }

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
