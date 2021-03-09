<?php

namespace Api\Domain\Events;

use Infrastructure\Events\Event;
use Api\Domain\Models\Domain;

class DomainWasCreated extends Event
{
    public $object;

    public $clearCacheUri;

    public function __construct(Domain $object, $clearCacheUri = null)
    {
      $this->object = $object;
      $this->clearCacheUri = $clearCacheUri;
    }
}
