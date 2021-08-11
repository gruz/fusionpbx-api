<?php

namespace Gruz\FPBX\Events;

use Gruz\FPBX\Models\AbstractModel;

class TeamWasCreated extends AbstractEvent
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
