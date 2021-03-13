<?php

namespace Api\User;

use Api\User\Events\TeamWasCreated;
use Api\User\Listeners\TeamWasCreatedListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class TeamServiceProvider extends ServiceProvider
{
    protected $listen = [
        TeamWasCreated::class => [
            TeamWasCreatedListener::class,
        ]
    ];

}
