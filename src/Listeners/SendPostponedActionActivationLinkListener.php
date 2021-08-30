<?php

namespace Gruz\FPBX\Listeners;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;
use Gruz\FPBX\Notifications\DomainSignupNotification;

class SendPostponedActionActivationLinkListener
{
    public function handle($event)
    {
        foreach ($event->rows as $model) {
            $email = $model->request['user_email'];
            Notification::route('mail', $email)
                ->notify(new DomainSignupNotification($model));
        }
    }
}
