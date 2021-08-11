<?php

namespace Gruz\FPBX\Listeners;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;
use Gruz\FPBX\Notifications\DomainSignupNotification;

class SendPostponedActionActivationLinkListener
{
    public function handle($event)
    {
        foreach ($event->users as $user) {
            $email = Arr::get($user, 'user_email');
            Notification::route('mail', $email)
                ->notify(new DomainSignupNotification($event->model));
        }
    }
}
