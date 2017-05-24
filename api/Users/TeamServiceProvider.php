<?php

namespace Api\Users;

// ~ use Illuminate\Events\EventServiceProvider;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

use Infrastructure\Listeners\ClearFusionPBXCache;

class TeamServiceProvider extends ServiceProvider
{
    protected $listen = [
        TeamWasCreated::class => [
            ClearFusionPBXCache::class,
        ]
    ];

}
