<?php

namespace Api\User\Events;

use App\Events\Event;
use App\Models\User;

class UserWasDeleted extends Event
{
    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
