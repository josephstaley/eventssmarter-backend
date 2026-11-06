<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Content;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContentPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Content $content)
    {
        return true;
    }

    public function create(User $user)
    {
        return true;
    }

    public function update(User $user, Content $content)
    {
        return $user->admin;
    }

    public function delete(User $user, Content $content)
    {
        return true;
    }
}
