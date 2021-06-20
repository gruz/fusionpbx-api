<?php

namespace Api\Settings\Events;

use App\Events\Event;
use App\Models\Setting;

class SettingWasDeleted extends Event
{
    public $setting;

    public function __construct(Setting $setting)
    {
        $this->setting = $setting;
    }
}
