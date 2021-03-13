<?php

namespace Api\User\Models;

use Api\User\Models\User;
use Api\User\Models\GroupPermission;
use Infrastructure\Database\Eloquent\Model;

class Group extends Model
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
