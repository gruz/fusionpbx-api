<?php

namespace App\Events;

use App\Events\Event;
use App\Models\PostponedAction;
use Illuminate\Support\Collection;

class PostponedActionWasCreated extends Event
{
    public $users;
    public $model;

    public function __construct(Collection $users, PostponedAction $model)
    {
        $this->users = $users;
        $this->model = $model;
    }
}