<?php

namespace Api\Status\Events;

use App\Events\Event;
use Api\Status\Models\Status;

class StatusWasCreated extends Event
{
    public $item;

    public function __construct(Status $item)
    {
        $this->item = $item;
    }
}
