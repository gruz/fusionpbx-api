<?php

namespace Api\Domain\Models;

use Infrastructure\Database\Eloquent\Model;
use Infrastructure\Traits\FusionPBXTableModel;

/**
 * @OA\Schema()
 */
class Domain_setting extends Model
{
    use FusionPBXTableModel;

    public $fillable = [
        // 'domain_uuid',
        // 'domain_setting_uuid',
        // 'app_uuid',
        'domain_setting_category',
        'domain_setting_subcategory',
        'domain_setting_name',
        'domain_setting_value',
        'domain_setting_order',
        'domain_setting_enabled',
        'domain_setting_description',
    ];

}
