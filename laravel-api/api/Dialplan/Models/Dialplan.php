<?php

namespace Api\Dialplan\Models;

use Api\User\Models\User;

use Illuminate\Notifications\Notifiable;
use App\Database\Eloquent\Model;
use App\Traits\FusionPBXTableModel;

class Dialplan extends Model
{
    use Notifiable, FusionPBXTableModel;

    public function details()
    {
        return $this->hasMany(Dialplan_details::class, 'dialplan_uuid', 'dialplan_uuid');
    }

}
