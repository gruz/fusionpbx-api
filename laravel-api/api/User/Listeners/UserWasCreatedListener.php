<?php

namespace Api\User\Listeners;

use Api\User\Mail\UserNew;

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