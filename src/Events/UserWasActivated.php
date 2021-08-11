<?php

namespace Gruz\FPBX\Events;

use Gruz\FPBX\Models\User;

class UserWasActivated extends AbstractEvent
{
    public $user;

    public $sendNotification;

    public function __construct(User $user, $sendNotification = true)
    {
        $this->user = $user;
        $this->sendNotification = $sendNotification;
    }
}
