<?php

namespace Gruz\FPBX\Models;

use Gruz\FPBX\Models\User;
use Gruz\FPBX\Models\GroupPermission;
use Gruz\FPBX\Models\AbstractModel;

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
