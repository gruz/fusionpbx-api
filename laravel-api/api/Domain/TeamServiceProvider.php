<?php

namespace Api\Domain;

use App\Events\TeamWasCreated;
use Api\Domain\Listeners\TeamWasCreatedListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class TeamServiceProvider extends ServiceProvider
{
    protected $listen = [
        TeamWasCreated::class => [
            TeamWasCreatedListener::class,
        ]
    ];

}
