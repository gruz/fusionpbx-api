<?php

namespace Api\User\Events;

use Infrastructure\Events\Event;
use Api\User\Models\User;

class UserWasDeleted extends Event
{
    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
