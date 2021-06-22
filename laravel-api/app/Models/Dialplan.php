<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use App\Models\AbstractModel;

class Dialplan extends AbstractModel
{
    use Notifiable;

    public function details()
    {
        return $this->hasMany(DialplanDetails::class, 'dialplan_uuid', 'dialplan_uuid');
    }

}
