<?php

namespace App\Events;

use App\Events\Event;
use App\Models\User;

class UserWasUpdated extends Event
{
    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
