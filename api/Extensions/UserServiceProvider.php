<?php

namespace Api\Users;

use Infrastructure\Events\EventServiceProvider;
use Api\Users\Events\RoleWasCreated;
use Api\Users\Events\RoleWasDeleted;
use Api\Users\Events\RoleWasUpdated;
use Api\Users\Events\UserWasCreated;
use Api\Users\Events\UserWasDeleted;
use Api\Users\Events\UserWasUpdated;

class UserServiceProvider extends EventServiceProvider
{
    protected $listen = [
        RoleWasCreated::class => [
            // listeners for when a role is created
        ],
        RoleWasDeleted::class => [
            // listeners for when a role is deleted
        ],
        RoleWasUpdated::class => [
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
        ]
    ];
}
