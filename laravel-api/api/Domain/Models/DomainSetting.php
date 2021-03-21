<?php

namespace Api\Domain\Models;

use Infrastructure\Database\Eloquent\AbstractModel;

/**
 * @OA\Schema(
 * description="Use your DB client to see available `domain_setting_category` and `domain_setting_subcategory` references.

 ```
SELECT x.* FROM public.v_default_settings x
    WHERE default_setting_category in ('domain' , 'email')
    ORDER BY default_setting_category
```"
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

    // protected $visible = [
    //     'domain_setting_category',
    //     'domain_setting_subcategory',
    //     'domain_setting_name',
    //     'domain_setting_value',
    //     'domain_setting_order',
    //     'domain_setting_enabled',
    //     'domain_setting_description',
    // ];

    // /**
    //  * Domain settings category
    //  *
    //  * @var string
    //  *
    //  * @OA\Property(
    //  *      enum={"domain", "email"},
    //  *      example="domain"
    //  * )
    //  */
    // public $domain_setting_category;

    // /**
    //  * Domain settings sub category
    //  *
    //  * @var string
    //  *
    //  * @OA\Property(
    //  *     oneOf={
    //  *          @OA\Schema(
    //  *               enum=FPBX_DEFAULT_SETTINGS_domain,
    //  *               example="language"
    //  *          ),
    //  *          @OA\Schema(
    //  *               enum=FPBX_DEFAULT_SETTINGS_email,
    //  *               example="smtp_from_name"
    //  *          ),
    //  *     }
    //  * )
    //  */
    // public $domain_setting_subcategory;

    // /**
    //  * Setting field type
    //  *
    //  * @var string
    //  *
    //  * @OA\Property(
    //  *     enum=FPBX_DEFAULT_SETTINGS_domain_FIELD_TYPES,
    //  *     example="code"
    //  * )
    //  */
    // public $domain_setting_name;

    // /**
    //  * Setting field type
    //  *
    //  * @var string
    //  *
    //  * @OA\Property(
    //  *     example="uk-ua"
    //  * )
    //  */
    // public $domain_setting_value;

}
