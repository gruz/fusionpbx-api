<?php

namespace Api\User\Listeners;

use Api\User\Mail\UserNew;
use Api\Domain\Mail\DomainNew;

class TeamWasCreatedListener
{
    public function handle($event)
    {
        dd('NOTIFY USERS');
        // $admins = $event->user->getDomainAdmins()->get();

        // foreach ($admins as $k => $admin) {
        //     $emails = [];

        //     foreach ($admin->emails as $k => $email) {
        //         $emails[] = $email->email_address;
        //     }

        //     if ($event->user->user_uuid !== $admin->user_uuid) {
        //         \Mail::to($emails)->send(new UserNew($event->user));
        //     } else {
        //         \Mail::to($emails)->send(new DomainNew($event->user));
        //     }
        // }
    }
}
