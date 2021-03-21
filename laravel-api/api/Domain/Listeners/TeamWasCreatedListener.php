<?php

namespace Api\Domain\Listeners;

use Api\User\Models\User;
use Illuminate\Support\Facades\Notification;

use Api\Domain\Notifications\DomainActivateActivatorNotification;
use Api\Domain\Notifications\DomainActivateMainAdminNotification;

class TeamWasCreatedListener
{
    public function handle($event)
    {
        $mainAdminEmail = config('mail.from.address');

        Notification::route('mail', $mainAdminEmail)
            ->notify(new DomainActivateMainAdminNotification($event->model, $event->activatorUserData['user_email']));

        $adminUser = User::where([
            ['domain_uuid', $event->activatorUserData['domain_uuid']],
            ['user_email', $event->activatorUserData['user_email']],
        ])->first();

        $notification = new DomainActivateActivatorNotification(
            $event->model,
            $event->activatorUserData['username'],
            $event->activatorUserData['password']
        );
        Notification::send($adminUser, $notification);
        // Notification::route('mail', $event->activatorUserData['user_email'])
        //     ->notify(new DomainActivateActivatorNotification(
        //         $event->model,
        //         $event->activatorUserData['username'],
        //         $event->activatorUserData['password']
        //     ));

        //     // dd($event->model->users);


        // Notification::route('mail', $mainAdminEmail)
        //     ->notify(new DomainActivateMainAdminNotification($event->model, $event->activatorEmail));

        //     Notification::route('mail', $email)
        //     ->notify(new DomainSignupNotification($event->model));
        // dd($event);

        // foreach ($event->users as $user) {
        //     $email = Arr::get($user, 'user_email');
        // }

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
