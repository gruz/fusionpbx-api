<?php

namespace Api\Settings\Models;

use Illuminate\Notifications\Notifiable;
use App\Database\Eloquent\Model;
use App\Traits\FusionPBXTableModel;

class Default_setting extends Model
{
    use Notifiable, FusionPBXTableModel;

    protected $fillable = [
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        // ~ 'password',
    ];

}
