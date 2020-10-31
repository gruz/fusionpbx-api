<?php

namespace Api\Extension;

use App\Events\EventServiceProvider;
use App\Listeners\ClearFusionPBXCache;
// ~ use Illuminate\Foundation\Support\Providers\EventServiceProvider;
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
