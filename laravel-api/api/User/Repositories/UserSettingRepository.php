<?php

namespace Api\User\Repositories;

use Api\User\Models\User_setting;
use Infrastructure\Database\Eloquent\Repository;

class UserSettingRepository extends Repository
{
    public function getModel()
    {
        return new User_setting();
    }

    

}
