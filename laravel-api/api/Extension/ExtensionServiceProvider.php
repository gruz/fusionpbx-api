<?php

namespace Api\Extension;


use App\Events\ExtensionWasCreated;
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
