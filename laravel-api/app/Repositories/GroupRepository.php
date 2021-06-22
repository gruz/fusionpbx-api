<?php

namespace App\Repositories;

use Illuminate\Database\Query\Builder;
use App\Repositories\AbstractRepository;

class GroupRepository extends AbstractRepository
{
    public function filterIsAgent(Builder $query, $method, $clauseOperator, $value, $in)
    {
        // check if value is true
        if ($value) {
            $query->whereIn('groups.name', ['Agent']);
        }
    }
}
