<?php

namespace Api\Extension\Events;

use App\Events\Event;
use Api\ExtensionModelsxtension;

class ExtensionWasDeleted extends Event
{
    public $extension;

    public function __construct(Extension $extension)
    {
        $this->extension = $extension;
    }
}
