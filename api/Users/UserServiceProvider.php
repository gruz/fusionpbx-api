<?php

namespace Api\Users;

// ~ use Infrastructure\Events\EventServiceProvider;
use Api\Users\Events\GroupWasCreated;
use Api\Users\Events\GroupWasDeleted;
use Api\Users\Events\GroupWasUpdated;
use Api\Users\Events\UserWasCreated;
use Api\Users\Events\UserWasDeleted;
use Api\Users\Events\UserWasUpdated;
use Api\Users\Events\DomainWasCreated;

use Infrastructure\Listeners\ClearFusionPBXCache;

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
        ],
        UserWasDeleted::class => [
            // listeners for when a user is deleted
        ],
        UserWasUpdated::class => [
            // listeners for when a user is updated
        ],
        DomainWasCreated::class => [
          // ~ ClearFusionPBXCache::class,
        ],
    ];
}
