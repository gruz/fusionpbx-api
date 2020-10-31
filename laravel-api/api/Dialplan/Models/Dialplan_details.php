<?php

namespace Api\Dialplan\Models;

use Illuminate\Notifications\Notifiable;
use App\Database\Eloquent\Model;
use App\Traits\FusionPBXTableModel;

class Dialplan_details extends Model
{
    use Notifiable, FusionPBXTableModel;

}
