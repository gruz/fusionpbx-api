<?php

namespace Api\Extension\Events;

use App\Events\Event;
use Api\ExtensionModelsxtension;

class ExtensionWasUpdated extends Event
{
    public $extension;

    public function __construct(Extension $extension)
    {
        $this->extension = $extension;
    }
}
