<?php

namespace App\Events;

use App\Events\Event;
use App\Models\Extension;


class ExtensionWasUpdated extends Event
{
    public $extension;

    public function __construct(Extension $extension)
    {
        $this->extension = $extension;
    }
}
