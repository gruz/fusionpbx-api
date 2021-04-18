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

    public function createResellerCodeRecord($reseller_code) {
        $this->setAttribute('default_setting_category', 'billing');
        $this->setAttribute('default_setting_subcategory', 'reseller_code');
        $this->setAttribute('default_setting_name', 'array');
        $this->setAttribute('default_setting_enabled', 1);
        $this->setAttribute('default_setting_value', $reseller_code);
        $this->save();
    }
}
