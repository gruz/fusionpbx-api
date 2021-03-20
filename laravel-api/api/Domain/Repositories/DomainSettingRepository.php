<?php

namespace Api\Domain\Repositories;

use Api\Domain\Models\Domain_setting;
use Infrastructure\Database\Eloquent\Repository;

class DomainSettingRepository extends Repository
{
    public function getModel()
    {
        return new Domain_setting();
    }

    

}
