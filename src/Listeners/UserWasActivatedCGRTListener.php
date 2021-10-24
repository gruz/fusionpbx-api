<?php

namespace Gruz\FPBX\Listeners;

use Gruz\FPBX\Models\User;
use Gruz\FPBX\Models\UserSetting;
use Gruz\FPBX\Services\CGRTService;

class UserWasActivatedCGRTListener
{
    public function handle($event)
    {
        if (!config('fpbx.cgrt.enabled')) {
            return;
        }

        /**
         * @var CGRTService
         */
        $cGRTService = app(CGRTService::class);

        $cGRTService->processNewUser($event->user);
    }
}
