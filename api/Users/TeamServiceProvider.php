<?php

namespace Api\Users;

// ~ use Illuminate\Events\EventServiceProvider;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;


use Api\Users\Events\DomainWasCreated;
use Api\Users\Listeners\ClearFusionPBXCache;

class TeamServiceProvider extends ServiceProvider
{
    protected $listen = [
        DomainWasCreated::class => [
            ClearFusionPBXCache::class,
        ]
    ];

}
