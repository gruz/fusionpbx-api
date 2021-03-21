<?php

namespace Api\User\Listeners;

use Illuminate\Support\Facades\Notification;

use Api\User\Notifications\UserWasActivatedSelfNotification;

class UserWasActivatedListener
{
    public function handle($event)
    {
        if ($event->sendNotification) {
            Notification::send($event->user, new UserWasActivatedSelfNotification($event->user));
        }
    }
}
