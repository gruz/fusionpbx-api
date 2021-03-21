<?php

namespace Api\Domain\Models;

use Infrastructure\Database\Eloquent\AbstractModel;

/**
 * @OA\Schema(
 * description="Use your DB client to see available `domain_setting_category` and `domain_setting_subcategory` references.
 *
 *  ```
 * SELECT x.* FROM public.v_default_settings x
 *     WHERE default_setting_category in ('domain' , 'email')
 *     ORDER BY default_setting_category
 * ```"
 * )
 */
class DomainSetting extends AbstractModel
{
    // protected $attributes = [
    //     'domain_setting_category' => 'domain',
    //     'domain_setting_enabled' => true,
    // ];

    protected $fillable = [
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
