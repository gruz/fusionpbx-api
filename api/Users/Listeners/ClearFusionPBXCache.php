<?php

namespace Api\Users\Listeners;

use Api\Users\Events\DomainWasCreated;

class ClearFusionPBXCache
{
    public function handle(DomainWasCreated $event)
    {
        // Access the order using $event->order...
    }
}