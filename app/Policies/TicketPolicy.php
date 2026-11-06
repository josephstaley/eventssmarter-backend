<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ticket;
use Illuminate\Auth\Access\HandlesAuthorization;

class TicketPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the ticket.
     *
     * @param  \App\User  $user
     * @param  \App\ticket  $ticket
     * @return mixed
     */
    public function view(User $user, ticket $ticket)
    {
        //
    }

    /**
     * Determine whether the user can create tickets.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the ticket.
     *
     * @param  \App\User  $user
     * @param  \App\ticket  $ticket
     * @return mixed
     */
    public function update(User $user, ticket $ticket)
    {
        return $user->events()->where('id',$ticket->event_id)->count() > 0 || session('admin_edit');
    }

    /**
     * Determine whether the user can delete the ticket.
     *
     * @param  \App\User  $user
     * @param  \App\ticket  $ticket
     * @return mixed
     */
    public function delete(User $user, ticket $ticket)
    {
        return $user->events()->where('id',$ticket->event_id)->count() > 0 || session('admin_edit');
    }
}
