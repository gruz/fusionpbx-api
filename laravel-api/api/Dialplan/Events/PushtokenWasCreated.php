<?php

namespace Api\Dialplan\Events;

use Infrastructure\Events\Event;
use Api\Dialplan\Models\Dialplan;

class PushtokenWasCreated extends Event
{
    public $item;

    public function __construct(Dialplan $item)
    {
        $this->item = $item;
    }
}
