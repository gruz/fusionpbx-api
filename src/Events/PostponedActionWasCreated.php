<?php

namespace Gruz\FPBX\Events;

use Gruz\FPBX\Models\PostponedAction;
use Illuminate\Support\Collection;

class PostponedActionWasCreated extends AbstractEvent
{
    public $users;
    public $model;

    public function __construct(Collection $users, PostponedAction $model)
    {
        $this->users = $users;
        $this->model = $model;
    }
}
