<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Event;
use Illuminate\Auth\Access\HandlesAuthorization;

class EventPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the event.
     *
     * @param  \App\User  $user
     * @param  \App\Event  $event
     * @return mixed
     */
    public function view(User $user, Event $event)
    {
        return true;
    }

    /**
     * Determine whether the user can create events.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the event.
     *
     * @param  \App\User  $user
     * @param  \App\Event  $event
     * @return mixed
     */
    public function update(User $user, Event $event)
    {
        return $user->events()->where('id',$event->id)->count() > 0 || session('admin_edit');
    }

    /**
     * Determine whether the user can delete the event.
     *
     * @param  \App\User  $user
     * @param  \App\Event  $event
     * @return mixed
     */
    public function delete(User $user, Event $event)
    {
        return $user->events()->where('id',$event->id)->count() > 0 || session('admin_edit');
    }
}
