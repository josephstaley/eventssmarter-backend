<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use RolfHaug\TokenAuth\LoginToken;

class UpdatedTicketsLink extends Notification
{
    use Queueable;
    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $options = [];
        $options['link_1'] = url('/tickets?').LoginToken::generate($this->user, true);
        $options['link_2'] = url('https://athleticjunction.com/store/');
        
        return (new MailMessage)->markdown('mail.tickets.updated', $options);
    }

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
