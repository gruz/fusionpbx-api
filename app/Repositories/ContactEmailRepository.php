<?php

namespace App\Repositories;

use App\Repositories\AbstractRepository;

class ContactEmailRepository extends AbstractRepository
{
    public function create(array $data, $options = [])
    {
        $model = $this->getModel();

        $model->domain_uuid = $data['domain_uuid'];
        $model->contact_uuid = $data['contact_uuid'];

        $model->fill($data);
        $model->save();

        return $model;
    }
}
