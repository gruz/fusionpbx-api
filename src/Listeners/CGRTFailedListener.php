<?php

namespace Gruz\FPBX\Listeners;

use Illuminate\Support\Facades\Notification;
use Gruz\FPBX\Notifications\CGRTFailedNotification;
use Illuminate\Support\Facades\Log;


class CGRTFailedListener
{
    public function handle($event)
    {
        $mainAdminEmail = config('mail.error_email');

        Notification::route('mail', $mainAdminEmail)
            ->notify(new CGRTFailedNotification($event));

        Log::error($event->response);
    }
}
