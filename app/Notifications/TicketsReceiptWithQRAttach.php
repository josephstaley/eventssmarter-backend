<?php

namespace App\Notifications;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TicketsReceiptWithQRAttach extends Notification
{
    use Queueable;
    public $user;
    public $vouchers;

    public function __construct($user, $vouchers)
    {
        $this->user = $user;
        $this->vouchers = $vouchers;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $options = [];
        $options['link_1'] = url(env('FRONTEND_URL') . '/dashboard');
        if (isset($this->organization->ad_link)) {
            $options['link_2'] = url($this->vouchers[0]->ticket->event->organization->ad_link);
        } else {
            $options['link_2'] = url('https://athleticjunction.com/store/');
        }


        $mail = (new MailMessage)->markdown('mail.tickets.paid', $options)->subject('Receipt for ' . $this->vouchers[0]->event->name);

        foreach ($this->vouchers as $voucher) {
            $data = [
                'voucher' => $voucher,
                'user' => $this->user
            ];
            $pdf = Pdf::loadView('pdf.voucherQRAttachment', $data);

            $mail->attachData($pdf->output(), 'ticket-' . $voucher->detail_text . '.pdf', ['mime' => 'application/pdf']);
        }

        return $mail;

    }

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
