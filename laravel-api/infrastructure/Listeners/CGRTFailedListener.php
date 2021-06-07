<?php

namespace Infrastructure\Listeners;

use Illuminate\Support\Facades\Notification;
use Infrastructure\Notification\CGRTFailedNotification;


class CGRTFailedListener
{
    public function handle($event)
    {
        $mainAdminEmail = config('app.contact_email');

        Notification::route('mail', $mainAdminEmail)
            ->notify(new CGRTFailedNotification($event));

        \Log::error($event->response);
    }
}
