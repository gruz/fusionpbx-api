<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Notification;

use App\Notifications\PaymentReceivedNotification;
use App\Notifications\PaymentReceivedAdminNotification;

class PaymentReceivedListener
{
    public function handle($event)
    {
        $mainAdminEmail = config('mail.from.address');

        Notification::route('mail', $mainAdminEmail)
            ->notify(new PaymentReceivedAdminNotification($event->user, $event->options));

        $event->user->notify(new PaymentReceivedNotification($event->user, $event->options));
    }
}
