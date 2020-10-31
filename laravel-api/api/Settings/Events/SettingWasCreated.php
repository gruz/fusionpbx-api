<?php

namespace Api\Settings\Events;

use App\Events\Event;
use Api\SettingsModelsxtension;

class SettingWasCreated extends Event
{
    public $setting;

    public function __construct(Setting $setting)
    {
        $this->setting = $setting;
    }
}
