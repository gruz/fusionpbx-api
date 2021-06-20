<?php

namespace App\Events;

use App\Events\Event;
use App\Models\Pushtoken;

class PushtokenWasCreated extends Event
{
    public $item;

    public function __construct(Pushtoken $item)
    {
        $this->item = $item;
    }
}
