<?php

namespace Api\Extensions\Events;

use Infrastructure\Events\Event;
use Api\ExtensionsModelsxtension;

class ExtensionWasUpdated extends Event
{
    public $extension;

    public function __construct(Extension $extension)
    {
        $this->extension = $extension;
    }
}
