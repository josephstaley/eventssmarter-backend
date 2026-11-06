<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class inviteCameraCrewTeam extends Mailable
{

    protected $eventname;
    protected $name;
    protected $link;

    public function __construct($eventname, $name, $link)
    {
        $this->eventname = $eventname;
        $this->name = $name;
        $this->link = $link;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // dd($this->batch->id);
        return $this->markdown('mail.events.inviteCameraCrewTeam')
                    ->subject('You are invited as camera crew team')
                    ->with([
                        'eventname' => $this->eventname,
                        'name' => $this->name,
                        'link' => $this->link
                    ]);
    }
}
