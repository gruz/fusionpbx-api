<?php

namespace App\Events;

use App\Models\User;

class UserWasCreated extends AbstractEvent
{
    public $user;

    public $options;

    public function __construct(User $user, $options = [])
    {
        $this->user = $user;
        $this->options = $options;
    }
}
