<?php

namespace Api\Domain\Events;

use Infrastructure\Events\Event;
use Infrastructure\Database\Eloquent\AbstractModel;

class TeamWasCreated extends Event
{
    public $model;
    public $users;
    public $activatorEmail;

    public function __construct(AbstractModel $model, $users, $activatorEmail)
    {
        $this->model = $model;
        $this->users = $users;
        $this->activatorEmail = $activatorEmail;
    }
}
