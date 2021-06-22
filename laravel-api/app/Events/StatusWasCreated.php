<?php

namespace App\Events;

use App\Models\Status;

class StatusWasCreated extends AbstractEvent
{
    public $item;

    public function __construct(Status $item)
    {
        $this->item = $item;
    }
}
