<?php

namespace Api\Status\Repositories;

use Api\Status\Models\Status;
use Infrastructure\Database\Eloquent\AbstractRepository;
use Illuminate\Database\Eloquent\Collection;

class StatusRepository extends AbstractRepository
{
    public function update(Status $model, array $data)
    {
        $model->fill($data);

        $model->save();

        return $model;
    }
}
