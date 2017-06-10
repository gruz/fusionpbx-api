<?php

namespace Api\Users\Models;

use Api\Users\Models\User;
use Api\Users\Models\Group_permission;
use App\Database\Eloquent\Model;
use App\Traits\FusionPBXTableModel;

class Group_user extends Model
{
    use FusionPBXTableModel;

    /**
     * Get the primary key for the model.
     *
     * // ##mygruz20170610031046
     * We override the native model function only to make
     * User->hasManyThrough method to work.
     * See User->permissions method for more information
     *
     * @return string
     */
    public function getKeyName()
    {
        return 'group_name';
    }

}
