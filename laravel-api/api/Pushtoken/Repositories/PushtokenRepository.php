<?php

namespace Api\Pushtoken\Repositories;

use Api\Pushtoken\Models\Pushtoken;
use App\Database\Eloquent\Repository;
use Illuminate\Database\Eloquent\Collection;

class PushtokenRepository extends Repository
{
    public function getModel()
    {
        return new Pushtoken();
    }

    public function create(array $data)
    {
        $model = $this->getModel();

        $model->fill($data);

        $model->save();

        return $model;
    }

}
