<?php

namespace Api\Extensions;

use Infrastructure\Events\EventServiceProvider;
use Infrastructure\Listeners\ClearFusionPBXCache;
// ~ use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Api\Extensions\Events\ExtensionWasCreated;

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
