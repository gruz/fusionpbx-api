<?php

namespace App\Providers;

use App\Events\TeamWasCreated;
use App\Events\UserWasCreated;
use App\Events\CGRTFailedEvent;
use App\Events\UserWasActivated;
use App\Listeners\CGRTFailedListener;
use Illuminate\Auth\Events\Registered;
use App\Events\PostponedActionWasCreated;
use App\Listeners\TeamWasCreatedListener;
use App\Listeners\UserWasCreatedListener;
use App\Listeners\UserWasActivatedListener;
use App\Listeners\UserWasActivatedCGRTListener;
use App\Listeners\SendPostponedActionActivationLinkListener;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        CGRTFailedEvent::class => [
            CGRTFailedListener::class,
        ],
        PostponedActionWasCreated::class => [
            SendPostponedActionActivationLinkListener::class,
        ],
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        /** FusionPBX Entities **/

        TeamWasCreated::class => [
            TeamWasCreatedListener::class,
        ],

        UserWasCreated::class => [
            UserWasCreatedListener::class,
        ],

        UserWasActivated::class => [
            UserWasActivatedListener::class,
            UserWasActivatedCGRTListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
