<?php

namespace Api\Users\Events;

use App\Events\Event;
use Api\Users\Models\User;

class UserWasCreated extends Event
{
    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
