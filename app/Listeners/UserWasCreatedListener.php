<?php

namespace App\Listeners;

use Illuminate\Support\Arr;
use Illuminate\Contracts\Auth\MustVerifyEmail;


class UserWasCreatedListener
{
    public function handle($event)
    {
        // Send veirification links to users
        if ($event->user instanceof MustVerifyEmail && ! $event->user->hasVerifiedEmail()) {

            $excludeNotificationEmails = Arr::get($event->options, 'excludeNotification', []);
            if (!in_array($event->user->user_email, $excludeNotificationEmails)) {
                $event->user->sendEmailVerificationNotification();
                // $notification = new UserWasCreatedSendVeirfyLinkNotification($event->user);
                // Notification::send($event->user, $notification);
            }
        }
    }
}
