<?php

namespace Api\Settings\Events;

use Infrastructure\Events\Event;
use Api\SettingsModelsxtension;

class SettingWasUpdated extends Event
{
    public $setting;

    public function __construct(Setting $setting)
    {
        $this->setting = $setting;
    }
}
