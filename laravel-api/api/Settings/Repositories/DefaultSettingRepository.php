<?php

namespace Api\Settings\Repositories;

use Api\Settings\Models\Default_setting;
use Infrastructure\Database\Eloquent\Repository;

class DefaultSettingRepository extends Repository
{
    public function getModel()
    {
        return new Default_setting();
    }

    

}
