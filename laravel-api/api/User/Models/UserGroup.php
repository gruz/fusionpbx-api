<?php

namespace Api\User\Models;

use Infrastructure\Database\Eloquent\Model;

class UserGroup extends Model
{
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
