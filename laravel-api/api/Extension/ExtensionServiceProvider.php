<?php

namespace Api\Extension;


use Api\Extension\Events\ExtensionWasCreated;
use Infrastructure\Listeners\ClearFusionPBXCache;
use Infrastructure\Providers\EventServiceProvider;

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
