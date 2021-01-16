<?php

namespace Api\Extension;

use Infrastructure\Events\EventServiceProvider;
use Infrastructure\Listeners\ClearFusionPBXCache;
use Api\Extension\Events\ExtensionWasCreated;

class ExtensionServiceProvider extends EventServiceProvider
{
    protected $listen = [
        ExtensionWasCreated::class => [
            ClearFusionPBXCache::class,
        ],
        ExtensionWasDeleted::class => [
            ClearFusionPBXCache::class,
        ],
        ExtensionWasUpdated::class => [
            ClearFusionPBXCache::class,
        ]
    ];
}
