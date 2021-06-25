<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Notification;
use App\Notifications\CGRTFailedNotification;


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
