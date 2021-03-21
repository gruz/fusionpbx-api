<?php

namespace Api\Settings\Models;

use Illuminate\Notifications\Notifiable;
use Infrastructure\Database\Eloquent\AbstractModel;

class DefaultSetting extends AbstractModel
{
    use Notifiable;

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
