<?php

namespace Api\PostponedAction;

use App\Events\PostponedActionWasCreated;
use Api\PostponedAction\Listeners\SendPostponedActionActivationLinkListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class PostponedActionServiceProvider extends ServiceProvider
{
    protected $listen = [
        PostponedActionWasCreated::class => [
            SendPostponedActionActivationLinkListener::class,
        ],
    ];
}
