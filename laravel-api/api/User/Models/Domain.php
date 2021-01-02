<?php
namespace Api\User\Models;

use Illuminate\Notifications\Notifiable;
use App\Database\Eloquent\Model;
use App\Traits\FusionPBXTableModel;

class Domain extends Model
{
    use Notifiable, FusionPBXTableModel;

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
