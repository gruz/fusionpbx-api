<?php

namespace App\Models;

use App\Models\User;
use App\Models\GroupPermission;
use App\Models\AbstractModel;

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
