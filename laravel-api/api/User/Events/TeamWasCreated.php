<?php

namespace Api\User\Events;

use Infrastructure\Events\Event;
use Infrastructure\Database\Eloquent\Model;

class TeamWasCreated extends Event
{
    public $model;
    public $users;

    public function __construct(Model $model, $users)
    {
        $this->model = $model;
        $this->users = $users;
    }
}
