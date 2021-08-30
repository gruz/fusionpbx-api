<?php

namespace Gruz\FPBX\Listeners;

use Gruz\FPBX\Models\User;

use Gruz\FPBX\Notifications\Notification;
use Gruz\FPBX\Notifications\DomainActivateActivatorNotification;
use Gruz\FPBX\Notifications\DomainActivateMainAdminNotification;

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
            $event->activatorUserData
        );
        Notification::send($adminUser, $notification);
    }
}
