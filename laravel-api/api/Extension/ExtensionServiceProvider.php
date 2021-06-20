<?php

namespace Api\Extension;


use Api\Extension\Events\ExtensionWasCreated;
use App\Listeners\ClearFusionPBXCache;
use App\Providers\EventServiceProvider;

class ExtensionServiceProvider extends EventServiceProvider
{
    protected $listen = [
        ExtensionWasCreated::class => [
            // ClearFusionPBXCache::class,
        ],
        ExtensionWasDeleted::class => [
            // ClearFusionPBXCache::class,
        ],
        ExtensionWasUpdated::class => [
            // ClearFusionPBXCache::class,
        ]
    ];
}
