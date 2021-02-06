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

    protected $visible = [
        'domain_uuid',
    ];

    /**
     * Domain settings category
     *
     * @var string
     *
     * @OA\Property(
     *     default="call_block",
     *     title="Order status",
     *     description="Order status",
     *     enum={"call_block", "approved", "delivered"},
     * )
     */
    public $domain_setting_category;

}
