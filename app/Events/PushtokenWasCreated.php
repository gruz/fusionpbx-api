<?php

namespace App\Events;

use App\Models\Pushtoken;

class PushtokenWasCreated extends AbstractEvent
{
    public $item;

    public function __construct(Pushtoken $item)
    {
        $this->item = $item;
    }
}
