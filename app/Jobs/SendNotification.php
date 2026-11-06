<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Notifications\NotificationMail;
use Notification;
use Illuminate\Support\Facades\Crypt;
use Twilio\Rest\Client;
use App\Models\VoucherBundle;

class SendNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 0; // Max tries
    public $timeout = 3600 * 10; // Timeout seconds

    public $ticket_ids;
    public $title;
    public $content;
    public $img_url;
    public $file_url;
    public $admin;

    public function __construct($ticket_ids, $title, $content, $img_url, $file_url, $admin)
    {
        $this->ticket_ids = $ticket_ids;
        $this->title = $title;
        $this->content = $content;
        if (isset($img_url) && $img_url && $img_url !== ''){
            $this->img_url = $img_url;
        }
        if (isset($file_url) && $file_url && $file_url !== ''){
            $this->file_url = $file_url;
        }
        $this->admin = $admin;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $vouchers = VoucherBundle::whereIn('ticket_id', $this->ticket_ids)->leftJoin('users', 'voucher_bundles.user_id', 'users.id')->where('users.email_unsubscribed', 0)->whereNull('users.deleted_at')->groupBy('voucher_bundles.user_id')->get();
        foreach ($vouchers as $voucher) {
            $user = $voucher->user;
            $unsubscribe_token = Crypt::encryptString($user->email);
            try {
                Notification::send($user, (new NotificationMail($this->title, $this->content, $this->img_url, $this->file_url, $unsubscribe_token)));
            } catch (\Throwable $th) {
                // if (app()->bound('sentry')) {
                //     app('sentry')->captureException($th);
                // }
            }
            try {
                if ($user->phone_number && $this->admin){
                    $last_name = (strpos($user->name, ' ') === false) ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $user->name);
                    $first_name = trim( preg_replace('#'.preg_quote($last_name,'#').'#', '', $user->name ));
                    $message = "Hi, ".$first_name."!\n ".$this->content."\nThanks for using EventsSmarter!\nIf you want to unsubscribe, please send 'STOP'.";
                    $account_sid = env("TWILIO_SID");
                    $auth_token = env("TWILIO_TOKEN");
                    $twilio_number = env("TWILIO_FROM");

                    $client = new Client($account_sid, $auth_token);
                    $client->messages->create($user->phone_number, [
                        'from' => $twilio_number,
                        'body' => $message]);
                }
            } catch (\Throwable $th) {
                // if (app()->bound('sentry')) {
                //     app('sentry')->captureException($th);
                // }
            }
        }
    }
}
