<?php

namespace Api\PostponedAction\Listeners;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;
use Api\Domain\Notifications\DomainSignupNotification;
use stdClass;

class SendPostponedActionActivationLink
{
    public function handle($event)
    {
        $users = [];
        foreach ($event->users as $user) {
            $recepient = new stdClass;
            $recepient->name = Arr::get($user, 'username');
            $recepient->email = Arr::get($user, 'user_email');
            $users[] = $recepient;
        }

        Notification::route('mail', $users)
            ->notify(new DomainSignupNotification($event->model));
    }
}
