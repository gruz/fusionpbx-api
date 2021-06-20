<?php

namespace App\Events;

use App\Events\Event;
use App\Models\Group;

class GroupWasDeleted extends Event
{
    public $group;

    public function __construct(Group $group)
    {
        $this->group = $group;
    }
}
