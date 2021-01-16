<?php

namespace Api\Dialplan\Models;

use Illuminate\Notifications\Notifiable;
use Infrastructure\Database\Eloquent\Model;
use Infrastructure\Traits\FusionPBXTableModel;

class Dialplan_details extends Model
{
    use Notifiable, FusionPBXTableModel;

}
