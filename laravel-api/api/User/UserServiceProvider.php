<?php

namespace Api\User;

use App\Events\UserWasCreated;
use App\Events\UserWasDeleted;
use App\Events\UserWasUpdated;
use App\Events\GroupWasCreated;
use App\Events\GroupWasDeleted;
use App\Events\GroupWasUpdated;
use App\Events\UserWasActivated;
use Api\User\Listeners\UserWasCreatedListener;
use Api\User\Listeners\UserWasActivatedListener;
use Api\User\Listeners\UserWasActivatedCGRTListener;
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
        UserWasActivated::class => [
            UserWasActivatedListener::class,
            UserWasActivatedCGRTListener::class,
        ],
    ];
}
