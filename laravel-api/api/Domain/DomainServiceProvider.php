<?php

namespace Api\Domain;

use Api\Domain\Events\DomainWasCreated;
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
