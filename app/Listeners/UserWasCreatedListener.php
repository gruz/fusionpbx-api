<?php

namespace App\Listeners;

use Api\Users\Mail\UserNew;

class UserWasCreatedListener
{
    public function handle($event)
    {
      \Mail::to('a@a.com')->send(new UserNew($event->user));
    }


}