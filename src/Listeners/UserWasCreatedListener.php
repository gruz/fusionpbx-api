<?php

namespace Gruz\FPBX\Listeners;

use Illuminate\Support\Arr;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class UserWasCreatedListener
{
    public function handle($event)
    {
        $a = 1;
        // Send veirification links to users
        if ($event->user instanceof MustVerifyEmail && ! $event->user->hasVerifiedEmail()) {

            $excludeNotificationEmails = Arr::get($event->options, 'excludeNotification', []);
            if (!in_array($event->user->user_email, $excludeNotificationEmails)) {
                $event->user->sendEmailVerificationNotification();
            }
        }
    }
}
