<?php

namespace Api\Extension\Events;

use Infrastructure\Events\Event;
use Api\Extension\Models\Extension;

class ExtensionWasCreated extends Event
{
    public $object;

    public $options;

    public function __construct(Extension $object, $options = null)
    {
      $this->object = $object;
      $this->options = $options;
    }
}
