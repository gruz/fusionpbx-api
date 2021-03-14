<?php

namespace Api\Dialplan\Models;

use Api\User\Models\User;

use Illuminate\Notifications\Notifiable;
use Infrastructure\Database\Eloquent\AbstractModel;

class Dialplan extends AbstractModel
{
    use Notifiable;

    public function details()
    {
        return $this->hasMany(DialplanDetails::class, 'dialplan_uuid', 'dialplan_uuid');
    }

}
