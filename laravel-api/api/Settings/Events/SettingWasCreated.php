<?php

namespace Api\Settings\Events;

use App\Events\Event;
use Infrastructure\Database\Eloquent\AbstractModel;

class SettingWasCreated extends Event
{
    public $setting;

    public function __construct(AbstractModel $setting)
    {
        $this->setting = $setting;
    }
}
