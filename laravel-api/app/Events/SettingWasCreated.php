<?php

namespace App\Events;

use App\Events\Event;
use App\Database\Eloquent\AbstractModel;

class SettingWasCreated extends Event
{
    public $setting;

    public function __construct(AbstractModel $setting)
    {
        $this->setting = $setting;
    }
}
