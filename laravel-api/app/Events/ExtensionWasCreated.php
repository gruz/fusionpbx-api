<?php

namespace App\Events;

use App\Events\Event;
use App\Models\Extension;

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
