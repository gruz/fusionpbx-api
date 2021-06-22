<?php

namespace App\Events;

use App\Events\Event;
use App\Models\AbstractModel;

class TeamWasCreated extends Event
{
    public $model;
    public $users;
    public $activatorUserData;

    public function __construct(AbstractModel $model, $users, $activatorUserData)
    {
        $this->model = $model;
        $this->users = $users;
        $this->activatorUserData = $activatorUserData;
    }
}
