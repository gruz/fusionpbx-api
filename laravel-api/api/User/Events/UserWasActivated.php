<?php

namespace Api\User\Events;

use Infrastructure\Events\Event;
use Api\User\Models\User;

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
