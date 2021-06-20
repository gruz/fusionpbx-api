<?php

namespace Api\Domain;

use App\Events\DomainWasCreated;
use App\Listeners\ClearFusionPBXCache;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class DomainServiceProvider extends ServiceProvider
{
    protected $listen = [
        DomainWasCreated::class => [
            // ClearFusionPBXCache::class,
        ],
    ];
}
