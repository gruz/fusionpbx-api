<?php

namespace App\Events;

use App\Events\Event;
use App\Models\Setting;

class SettingWasUpdated extends Event
{
    public $setting;

    public function __construct(Setting $setting)
    {
        $this->setting = $setting;
    }
}
