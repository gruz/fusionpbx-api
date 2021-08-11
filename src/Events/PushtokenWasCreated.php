<?php

namespace Gruz\FPBX\Events;

use Gruz\FPBX\Models\Pushtoken;

class PushtokenWasCreated extends AbstractEvent
{
    public $item;

    public function __construct(Pushtoken $item)
    {
        $this->item = $item;
    }
}
