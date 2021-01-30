<?php

namespace Api\Status\Repositories;

use Api\Status\Models\Status;
use Infrastructure\Database\Eloquent\Repository;
use Illuminate\Database\Eloquent\Collection;

class StatusRepository extends Repository
{
    public function getModel()
    {
        return new Status();
    }

    public function update(Status $model, array $data)
    {
        $model->fill($data);

        $model->save();

        return $model;
    }
}
