<?php

namespace Api\User\Models;

use Api\User\Models\User;
use Api\User\Models\GroupPermission;
use Infrastructure\Database\Eloquent\AbstractModel;

class Group extends AbstractModel
{
    protected $fillable = [
        'name'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'v_user_groups', 'user_uuid', 'group_uuid');
    }

    public function permissions()
    {
        return $this->hasMany(GroupPermission::class, 'group_name', 'group_name');
    }
}
