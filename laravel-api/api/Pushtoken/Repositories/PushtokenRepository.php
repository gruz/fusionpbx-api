<?php

namespace Api\Pushtoken\Repositories;

use Api\Pushtoken\Models\Pushtoken;
use Infrastructure\Database\Eloquent\Repository;
use Illuminate\Database\Eloquent\Collection;

class PushtokenRepository extends Repository
{
    public function getModel()
    {
        return new Pushtoken();
    }
}
