<?php

namespace Api\User\Listeners;

use Api\User\Mail\UserNew;
use Api\User\Mail\DomainNew;
use Illuminate\Support\Facades\Password;
use Api\User\Mail\ResetPasswordLink;


class ResetPasswordLinkWasRequestedListener
{
    public function handle($event)
    {
        // 1
        $emails = $event->user->getEmailForPasswordReset();
        \Mail::to($emails)->send(new ResetPasswordLink($event->user, $event->token));

        // 2
        // $token = Password::createToken($event->user);
        // then send message with token....
    
        // 3 
        // $status = Password::sendResetLink($event->user->toArray());
    }


}