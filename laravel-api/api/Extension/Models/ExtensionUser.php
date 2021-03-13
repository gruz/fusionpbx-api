<?php

namespace Api\Extension\Models;

use Illuminate\Notifications\Notifiable;
use Infrastructure\Database\Eloquent\Model;

class ExtensionUser extends Model
{
    use Notifiable;

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
