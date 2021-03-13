<?php

namespace Api\Settings\Events;

use Infrastructure\Events\Event;
use Infrastructure\Database\Eloquent\Model;

class SettingWasCreated extends Event
{
    public $setting;

    public function __construct(Model $setting)
    {
        $this->setting = $setting;
    }
}
