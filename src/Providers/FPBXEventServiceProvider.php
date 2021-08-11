<?php

namespace Gruz\FPBX\Providers;

use Gruz\FPBX\Events\TeamWasCreated;
use Gruz\FPBX\Events\UserWasCreated;
use Gruz\FPBX\Events\CGRTFailedEvent;
use Gruz\FPBX\Events\UserWasActivated;
use Gruz\FPBX\Listeners\CGRTFailedListener;
use Illuminate\Auth\Events\Registered;
use Gruz\FPBX\Events\PostponedActionWasCreated;
use Gruz\FPBX\Listeners\TeamWasCreatedListener;
use Gruz\FPBX\Listeners\UserWasCreatedListener;
use Gruz\FPBX\Listeners\UserWasActivatedListener;
use Gruz\FPBX\Listeners\UserWasActivatedCGRTListener;
use Gruz\FPBX\Listeners\SendPostponedActionActivationLinkListener;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class FPBXEventServiceProvider extends ServiceProvider
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
        ]
    ];
}
