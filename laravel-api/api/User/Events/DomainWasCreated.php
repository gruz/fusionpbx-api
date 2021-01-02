<?php

namespace Api\User\Events;

use App\Events\Event;
use Api\User\Models\Domain;

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
