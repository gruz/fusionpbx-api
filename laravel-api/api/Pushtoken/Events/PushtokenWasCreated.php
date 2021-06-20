<?php

namespace Api\Pushtoken\Events;

use App\Events\Event;
use Api\Pushtoken\Models\Pushtoken;

class PushtokenWasCreated extends Event
{
    public $item;

    public function __construct(Pushtoken $item)
    {
        $this->item = $item;
    }
}
