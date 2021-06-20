<?php

namespace App\Events;

use App\Events\Event;
use App\Models\Status;

class StatusWasCreated extends Event
{
    public $item;

    public function __construct(Status $item)
    {
        $this->item = $item;
    }
}
