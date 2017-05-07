<?php

namespace Api\Users\Repositories;

use Api\Users\Models\Role;
use Illuminate\Database\Query\Builder;
use Infrastructure\Database\Eloquent\Repository;

class RoleRepository extends Repository
{
    public function getModel()
    {
        return new Role();
    }

    public function create(array $data)
    {
        $role = $this->getModel();

        $role->fill($data);
        $role->save();

        return $role;
    }

    public function update(Role $role, array $data)
    {
        $role->fill($data);

        $role->save();

        return $role;
    }

    public function filterIsAgent(Builder $query, $method, $clauseOperator, $value, $in)
    {
        // check if value is true
        if ($value) {
            $query->whereIn('roles.name', ['Agent']);
        }
    }
}
