<?php

namespace Api\User\Events;

use App\Events\Event;
use Api\User\Models\Group;

class GroupWasDeleted extends Event
{
    public $group;

    public function __construct(Group $group)
    {
        $this->group = $group;
    }
}
