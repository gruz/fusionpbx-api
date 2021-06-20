<?php

namespace App\Events;

use App\Events\Event;
use App\Models\User;

class UserWasCreated extends Event
{
    public $user;

    public $options;

    public function __construct(User $user, $options = [])
    {
        $this->user = $user;
        $this->options = $options;
    }
}
