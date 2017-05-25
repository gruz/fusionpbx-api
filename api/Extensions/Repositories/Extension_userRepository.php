<?php

namespace Api\Extensions\Repositories;

use Api\Extensions\Models\Extension_user;
use Infrastructure\Database\Eloquent\Repository;

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
