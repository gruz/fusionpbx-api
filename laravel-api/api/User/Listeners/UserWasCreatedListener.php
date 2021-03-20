<?php

namespace Api\User\Listeners;

use Api\User\Mail\UserNew;
use Illuminate\Support\Arr;
use Api\Domain\Mail\DomainNew;
use Infrastructure\Notification\Notification;
use Api\User\Notifications\UserWasCreatedSendVeirfyLinkNotification;

class UserWasCreatedListener
{
    public function handle($event)
    {
        // Send veirification links to users
        if ($event->user->getAttribute('user_enabled') !== true) {
            $excludeNotificationEmails = Arr::get($event->options, 'excludeNotification', []);
            if (!in_array($event->user->user_email, $excludeNotificationEmails)) {
                $notification = new UserWasCreatedSendVeirfyLinkNotification($event->user);
                Notification::send($event->user, $notification);
            }
        }

        return;
        // Notify domain admins on new user registration

        $admins = $event->user->getDomainAdmins()->get();

        foreach ($admins as $k => $admin) {
            $emails = [];

            foreach ($admin->emails as $k => $email) {
                $emails[] = $email->email_address;
            }

            if ($event->user->user_uuid !== $admin->user_uuid) {
                \Mail::to($emails)->send(new UserNew($event->user));
            } else {
                \Mail::to($emails)->send(new DomainNew($event->user));
            }
        }
    }
}
