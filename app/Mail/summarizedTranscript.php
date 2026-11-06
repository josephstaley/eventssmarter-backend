<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class summarizedTranscript extends Mailable
{
    protected $text;

    public function __construct($text)
    {
        $this->text = $text;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // dd($this->batch->id);
        return $this->markdown('mail.events.summarizedTranscript')
            ->subject('Meeting transcript summary')
            ->with([
                'text' => $this->text,
            ]);
    }
}
