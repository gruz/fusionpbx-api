<?php

namespace Api\User\Events;

use App\Events\Event;
use App\Models\User;

class UserWasActivated extends Event
{
    public $user;

    public $sendNotification;

    public function __construct(User $user, $sendNotification = true)
    {
        $this->user = $user;
        $this->sendNotification = $sendNotification;
    }
}
