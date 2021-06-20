<?php

namespace Api\Domain\Models;

use Api\User\Models\User;
use Illuminate\Notifications\Notifiable;
use App\Database\Eloquent\AbstractModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @OA\Schema()
 */
class Domain extends AbstractModel
{
    use HasFactory;
    use Notifiable;

    /**
     * Domain name
     *
     * @var string
     * @OA\Property(
     *  format="hostname",
     * )
     */
    public $domain_name;

    /**
     * Domain desctiption. Automatically generated upon domain creation if nothing passed
     * @var string
     * @OA\Property(
     *      example="Created via api at 2017-05-17 14:30:10",
     * )
     */
    public $domain_description;

    /**
     * Parent domain id
     *
     * @var uuid|null
     *
     * @OA\Property(
     *   nullable=true,
     * )
     */
    public $domain_parent_uuid;

    // public function __construct(array $attributes = [])
    // {
    //     parent::__construct($attributes);

    //     $this->attributes['domain_description']  = config('domain.description');
    // }

    // protected $attributes = [
    // ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'domain_name',
        'domain_description',
        'domain_parent_uuid'
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

    public function domain_settings()
    {
        return $this->hasMany(DomainSetting::class, 'domain_uuid');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'domain_uuid', 'domain_uuid');
    }
}
