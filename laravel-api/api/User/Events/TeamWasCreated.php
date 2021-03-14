<?php

namespace Api\User\Events;

use Infrastructure\Events\Event;
use Infrastructure\Database\Eloquent\AbstractModel;

class TeamWasCreated extends Event
{
    public $model;
    public $users;

    public function __construct(AbstractModel $model, $users)
    {
        $this->model = $model;
        $this->users = $users;
    }
}
