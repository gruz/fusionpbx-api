<?php

namespace Api\PostponedAction;

use Api\PostponedAction\Events\PostponedActionWasCreated;
use Api\PostponedAction\Listeners\SendPostponedActionActivationLink;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class PostponedActionServiceProvider extends ServiceProvider
{
    protected $listen = [
        PostponedActionWasCreated::class => [
            SendPostponedActionActivationLink::class,
        ],
    ];
}
