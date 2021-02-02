<?php

namespace Infrastructure\Events;

use Infrastructure\Events\Event;
use Api\User\Models\User;

class ResetPasswordLinkWasRequested extends Event
{
    public $user;

    public $token;

    public function __construct(User $user, $token)
    {
        $this->user = $user;
        $this->token = $token;
    }
}
