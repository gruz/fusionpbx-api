<?php

namespace Api\User\Events;

use App\Events\Event;
use Api\User\Models\User;

class UserWasUpdated extends Event
{
    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
