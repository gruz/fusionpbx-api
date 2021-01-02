<?php

namespace Api\Extension\Repositories;

use Api\Extension\Models\Extension_user;
use App\Database\Eloquent\Repository;

class Extension_userRepository extends Repository
{
    public function getModel()
    {
        return new Extension_user();
    }

    public function create(array $data)
    {
        $extension = $this->getModel();

        $extension->fill($data);
        $extension->save();

        return $extension;
    }

    public function update(Extension_user $extension, array $data)
    {
        $extension->fill($data);

        $extension->save();

        return $extension;
    }

}
