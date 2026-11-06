<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class UserCreated extends Notification
{
    use Queueable;
    public $password;

    public function __construct($password)
    {
        $this->password = $password;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $options = [];
        $options['password'] = $this->password;

        return (new MailMessage)->markdown('mail.auth.userCreated', $options);
    }

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
