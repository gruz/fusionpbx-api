<?php

namespace App\Listeners;

use Illuminate\Support\Arr;
use App\Notifications\Notification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\UserWasCreatedSendVeirfyLinkNotification;


class UserWasCreatedListener
{
    public function handle($event)
    {
        // Send veirification links to users
        if ($event->user instanceof MustVerifyEmail && ! $event->user->hasVerifiedEmail()) {

            $excludeNotificationEmails = Arr::get($event->options, 'excludeNotification', []);
            if (!in_array($event->user->user_email, $excludeNotificationEmails)) {
                $event->user->sendEmailVerificationNotification();
                $notification = new UserWasCreatedSendVeirfyLinkNotification($event->user);
                Notification::send($event->user, $notification);
            }
        }
    }
}
