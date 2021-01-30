<?php
namespace Api\Domain\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Notifications\Notifiable;
use Infrastructure\Database\Eloquent\Model;
use Infrastructure\Traits\FusionPBXTableModel;

/**
 * @OA\Schema()
 */
class Domain extends Model
{
    use Notifiable, FusionPBXTableModel;

    /**
     * Domain id
     * @var uuid
     * @OA\Property(
     *  schema="domain_uuid",
     *  readOnly=true,
     *  example="54cd6070-3b0d-11e7-bf5a-4be762d404ce"
     * )
     */

     public $domain_uuid;

    /**
     * Domain or subdomain name
     * @var string
     * @OA\Property(
     *  example="site.com"
     * )
     */
    public $domain_name;

    /**
     * If domain is active
     * @var boolean
     * @OA\Property(
     *  example=false
     * )
     */
    public $domain_enabled;

    /**
     * Domain desctiption. Automatically generated upon domain creation if nothing passed
     * @var string
     * @OA\Property(
     *      example="Created via api at 2017-05-17 14:30:10",
     * )
     */
    public $domain_description;

    /**
     * Parant domain id
     * @var uuid|null
     * @OA\Property(
     *  x={"final"=true},
        example="54cdc4b0-3b0d-11e7-888f-c38f274a1cd2"
     * )
     */
    public $domain_parent_uuid;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'domain_name', 'domain_enabled', 'domain_description', 'domain_parent_uuid'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        //'password', 'remember_token',
    ];

    /**
     * Groups relation
     *
     * @return BelongsToMany
     */
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'v_user_groups', 'user_uuid', 'group_uuid');
    }

    /**
     * Special relationship to enable a resource filter
     * 'isAgent' for the user resource.
     * The isAgent filter will automatically eager load groups,
     * and thus need to know the groups relationship.
     */
    public function isAgent()
    {
        return $this->groups();
    }
}
