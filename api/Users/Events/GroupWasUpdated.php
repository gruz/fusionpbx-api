<?php

namespace Api\Users\Events;

use App\Events\Event;
use Api\Users\Models\Group;

class GroupWasUpdated extends Event
{
    public $group;

    public function __construct(Group $group)
    {
        $this->group = $group;
    }
}
