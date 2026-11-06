<?php

namespace App\Models;
use Carbon\Carbon;

class MyEvents
{
    private $for;
    private $without;
    private $with = [];

    function for(User $for)
    {
        $this->for = $for;
        return $this;
    }

    function without($without)
    {
        $this->without = $without;
        return $this;
    }

    function with($with)
    {
        $this->with = $with;
        return $this;
    }

    function limitByTickets($query){
        return $query->whereHas('tickets', function ($query) {
            return $query->whereIn('id', $this->with)->withTrashed();
        });
    }

    function limitWithout($query){
        return $query->whereNotIn('id', $this->without);
    }

    function isEmpty()
    {
        return true;
    }

    function paginate()
    {
        if ($this->for) {
            $this->with = $this->for->vouchers()
                ->where('refunded_at', null)
                ->groupBy('ticket_id')
                ->limit(100)
                ->get()
                ->pluck('ticket_id')
                ->toArray();
        }

        if($this->for && empty($this->with)){
            return [];
        }
        $dateTime = null;
        if (!$dateTime instanceof \DateTime) {
            $dateTime = Carbon::parse($dateTime);
        }
        return Event::query()
            ->where('ends_at', '>', $dateTime)
            ->when($this->with,[$this,'limitByTickets'])
            ->when($this->without,[$this,'limitWithout'])
            ->paginate();
    }
}
