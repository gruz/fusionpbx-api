<?php

namespace Api\Users\Events;

use Infrastructure\Events\Event;
use Api\Users\Models\Domain;

class DomainWasCreated extends Event
{
    public $domain;

    public function __construct(Domain $domain)
    {
        $this->domain = $domain;
    }
}
