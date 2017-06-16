<?php

namespace Api\Extension\Models;

use Illuminate\Notifications\Notifiable;
use App\Database\Eloquent\Model;
use App\Traits\FusionPBXTableModel;

class Extension_user extends Model
{
    use Notifiable, FusionPBXTableModel;

    protected $fillable = [
        'domain_uuid',
        'extension_uuid',
        'user_uuid',
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
