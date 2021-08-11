<?php

namespace Gruz\FPBX\Events;

use Gruz\FPBX\Models\Status;

class StatusWasCreated extends AbstractEvent
{
    public $item;

    public function __construct(Status $item)
    {
        $this->item = $item;
    }
}
