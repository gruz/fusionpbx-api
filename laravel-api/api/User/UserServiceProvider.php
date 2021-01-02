<?php

namespace Api\User;

// ~ use App\Events\EventServiceProvider;
use Api\User\Events\UserWasCreated;
use Api\User\Events\UserWasDeleted;
use Api\User\Events\UserWasUpdated;
use Api\User\Events\GroupWasCreated;
use Api\User\Events\GroupWasDeleted;
use Api\User\Events\GroupWasUpdated;
use Api\User\Events\DomainWasCreated;

use App\Listeners\ClearFusionPBXCache;

use Api\User\Listeners\UserWasCreatedListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class UserServiceProvider extends ServiceProvider
{
    protected $listen = [
        GroupWasCreated::class => [
            // listeners for when a role is created
        ],
        GroupWasDeleted::class => [
            // listeners for when a role is deleted
        ],
        GroupWasUpdated::class => [
            // listeners for when a role is updated
        ],
        UserWasCreated::class => [
            // listeners for when a user is created
            UserWasCreatedListener::class,
        ],
        UserWasDeleted::class => [
            // listeners for when a user is deleted
        ],
        UserWasUpdated::class => [
            // listeners for when a user is updated
        ],
        DomainWasCreated::class => [
            ClearFusionPBXCache::class,
        ],
    ];
}
