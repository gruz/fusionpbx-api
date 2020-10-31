<?php

namespace Api\User\Repositories;

use Api\User\Models\Group;
use Illuminate\Database\Query\Builder;
use App\Database\Eloquent\Repository;

class GroupRepository extends Repository
{
    public function getModel()
    {
        return new Group();
    }

    public function create(array $data)
    {
        $group = $this->getModel();

        $group->fill($data);
        $group->save();

        return $group;
    }

    public function update(Group $group, array $data)
    {
        $group->fill($data);

        $group->save();

        return $group;
    }

    public function filterIsAgent(Builder $query, $method, $clauseOperator, $value, $in)
    {
        // check if value is true
        if ($value) {
            $query->whereIn('groups.name', ['Agent']);
        }
    }
}
