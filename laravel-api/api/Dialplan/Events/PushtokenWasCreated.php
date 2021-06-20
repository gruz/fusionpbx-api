<?php

namespace Api\Dialplan\Events;

use App\Events\Event;
use App\Models\Dialplan;

class PushtokenWasCreated extends Event
{
    public $item;

    public function __construct(Dialplan $item)
    {
        $this->item = $item;
    }
}
