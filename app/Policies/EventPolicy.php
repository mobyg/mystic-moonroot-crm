<?php
// app/Policies/EventPolicy.php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EventPolicy
{
    use HandlesAuthorization;

    public function update(User $user, Event $event)
    {
        return $user->id === $event->user_id;
    }

    public function delete(User $user, Event $event)
    {
        return $user->id === $event->user_id;
    }
}