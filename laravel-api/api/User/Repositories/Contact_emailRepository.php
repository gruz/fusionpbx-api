<?php

namespace Api\User\Repositories;

use Api\User\Models\Contact_email;
use Infrastructure\Database\Eloquent\Repository;

class Contact_emailRepository extends Repository
{
    public function getModel()
    {
        return new Contact_email();
    }

    public function create(array $data)
    {
        $model = $this->getModel();

        // ~ $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        
        $model->domain_uuid = $data['domain_uuid'];
        $model->contact_uuid = $data['contact_uuid'];

        $model->fill($data);
        $model->save();

        return $model;
    }

    public function update(Contact_email $user, array $data)
    {
        $user->fill($data);

        $user->save();

        return $user;
    }

}
