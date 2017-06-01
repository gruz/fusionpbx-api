<?php

namespace Api\Users\Models;

use Api\Users\Models\User;
use App\Database\Eloquent\Model;
use App\Traits\FusionPBXTableModel;

class Group extends Model
{
    use FusionPBXTableModel;

    protected $fillable = [
        'name'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'v_group_users', 'user_uuid', 'group_uuid');
    }
}
