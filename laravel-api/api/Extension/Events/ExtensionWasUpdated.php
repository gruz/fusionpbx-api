<?php

namespace Api\Extension\Events;

use Infrastructure\Events\Event;
use Api\Extension\Models\Extension;


class ExtensionWasUpdated extends Event
{
    public $extension;

    public function __construct(Extension $extension)
    {
        $this->extension = $extension;
    }
}
