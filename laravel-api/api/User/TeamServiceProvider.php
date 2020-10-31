<?php

namespace Api\User;

// ~ use Illuminate\Events\EventServiceProvider;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

use App\Listeners\ClearFusionPBXCache;

class TeamServiceProvider extends ServiceProvider
{
    protected $listen = [
        TeamWasCreated::class => [
            ClearFusionPBXCache::class,
        ]
    ];

}
