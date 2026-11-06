<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use RolfHaug\TokenAuth\LoginToken;

class TicketsAvailable extends Notification
{
    use Queueable;
    public $user;
    public $organization;

    public function __construct($user, $organization)
    {
        $this->user = $user;
        $this->organization = $organization;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $options = [];
        $options['link_1'] = url(env('FRONTEND_URL').'/dashboard');
        if (isset($this->organization->ad_link)){
            $options['link_2'] = url($this->organization->ad_link);
        }
        else {
            $options['link_2'] = url('https://athleticjunction.com/store/');
        }

        return (new MailMessage)->markdown('mail.tickets.paid', $options);
    }

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
