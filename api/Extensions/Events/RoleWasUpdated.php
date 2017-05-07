<?php

namespace Api\Users\Events;

use Infrastructure\Events\Event;
use Api\Users\Models\Role;

class RoleWasUpdated extends Event
{
    public $role;

    public function __construct(Role $role)
    {
        $this->role = $role;
    }
}
