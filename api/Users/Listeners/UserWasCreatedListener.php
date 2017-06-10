<?php

namespace Api\Users\Listeners;

use Api\Users\Mail\UserNew;

class UserWasCreatedListener
{
    public function handle($event)
    {
      $admins = $event->user->getDomainAdmins()->get();

      foreach ($admins as $k => $admin)
      {
        $emails = [];

        foreach ($admin->emails as $k => $email)
        {
          $emails[] = $email->email_address;
        }

        \Mail::to($emails)->send(new UserNew($event->user));

      }

    }


}