<?php

namespace Api\User\Models;

use Infrastructure\Database\Eloquent\Model;
use Infrastructure\Traits\FusionPBXTableModel;

/**
 * @OA\Schema()
 */
class User_setting extends Model
{
    use FusionPBXTableModel;

    public $fillable = [
        // 'user_setting_uuid',
        // 'user_uuid',
        // 'domain_uuid',
        'user_setting_category',
        'user_setting_subcategory',
        'user_setting_name',
        'user_setting_value',
        'user_setting_order',
        'user_setting_enabled',
        'user_setting_description',
    ];

}
