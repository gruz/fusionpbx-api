<?php

namespace Gruz\FPBX\Listeners;

use Illuminate\Support\Facades\Notification;
use Gruz\FPBX\Notifications\UserWasActivatedSelfNotification;
use Gruz\FPBX\Notifications\UserWasActivatedDomainAdminNotification;

class UserWasActivatedListener
{
    public function handle($event)
    {
        if ($event->sendNotification) {
            \Gruz\FPBX\Notifications\Notification::send($event->user, new UserWasActivatedSelfNotification($event->user));
        }

        $admins = $event->user->getDomainAdmins();
        \Gruz\FPBX\Notifications\Notification::send($admins, new UserWasActivatedDomainAdminNotification($event->user));

        $mainAdminEmail = config('mail.new_registration_email');

        Notification::route('mail', $mainAdminEmail)
            ->notify(new UserWasActivatedDomainAdminNotification($event->user));


        /**
         * @var \Gruz\FPBX\Models\User
         */
        $user = $event->user;
        $user->extensions()->update(['enabled' => 'true']);
    }
}
