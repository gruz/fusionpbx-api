<?php

namespace Api\Domain\Events;

use App\Events\Event;
use Api\Domain\Models\Domain;

class DomainWasCreated extends Event
{
    public $object;

    public $options;

    public function __construct(Domain $object, $options = null)
    {
      $this->object = $object;
      $this->options = $options;
    }
}
