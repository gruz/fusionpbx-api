<?php

namespace Gruz\FPBX\Models;

use Illuminate\Notifications\Notifiable;
use Gruz\FPBX\Models\AbstractModel;

class ExtensionUser extends AbstractModel
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