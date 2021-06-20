<?php

namespace Api\Settings;

use App\Providers\EventServiceProvider;

class SettingServiceProvider extends EventServiceProvider
{
    protected $listen = [
        SettingWasCreated::class => [
            // listeners for when a user is created
        ],
        SettingWasDeleted::class => [
            // listeners for when a user is deleted
        ],
        SettingWasUpdated::class => [
            // listeners for when a user is updated
        ]
    ];
}
