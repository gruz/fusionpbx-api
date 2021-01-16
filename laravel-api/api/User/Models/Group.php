<?php

namespace Api\User\Models;

use Api\User\Models\User;
use Api\User\Models\Group_permission;
use Infrastructure\Database\Eloquent\Model;
use Infrastructure\Traits\FusionPBXTableModel;

class Group extends Model
{
    use FusionPBXTableModel;

    protected $fillable = [
        'name'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'v_user_groups', 'user_uuid', 'group_uuid');
    }

    public function permissions()
    {
        return $this->hasMany(Group_permission::class, 'group_name', 'group_name');
    }
}
