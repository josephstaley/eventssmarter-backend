<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PromoRegister extends Notification
{
    use Queueable;
    public $organization;
    public $promo_code;

    public function __construct($organization, $promo_code)
    {
        $this->organization = $organization;
        $this->promo_code = $promo_code;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $connect_url = env('FRONTEND_URL') . '/organization/' . $this->organization->slug . '/affiliate/' . $this->promo_code;
        return (new MailMessage)
                    ->line('You are registered as affiliate of the organization called ' . $this->organization->name . '.')
                    ->action('Input your bank information for payout', $connect_url)
                    ->line('Your Promo Code is ' . $this->promo_code)
                    ->line('Thank you for using '.env('APP_NAME'));
    }

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
