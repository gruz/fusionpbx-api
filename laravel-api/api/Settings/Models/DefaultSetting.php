<?php

namespace Api\Settings\Models;

use Illuminate\Notifications\Notifiable;
use Infrastructure\Database\Eloquent\Model;

class DefaultSetting extends Model
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
