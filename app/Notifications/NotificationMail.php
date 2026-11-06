<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Queue\SerializesModels;

class NotificationMail extends Notification
{
    use Queueable;
    public $message;
    public $title;
    public $img;
    public $file;
    public $unsubscribe_token;

    public function __construct($title, $message, $img, $file, $unsubscribe_token)
    {
        $this->message = $message;
        $this->title = $title;
        $this->file = $file;
        $this->img = $img;
        $this->unsubscribe_token = $unsubscribe_token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $options = [];
        $options['content'] = $this->message;
        $options['img'] = $this->img;
        $options['unsubscribe_token'] = $this->unsubscribe_token;
        if ($options['img']){
            $mailMessage = (new MailMessage)
            ->subject($this->title)
            ->markdown('mail.events.pushNotificationImage', $options);
        }
        else $mailMessage = (new MailMessage)
                    ->subject($this->title)
                    ->markdown('mail.events.pushNotification', $options);
                    
        if ($this->file) $mailMessage->attach($this->file);
        
        return $mailMessage;
    }

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
