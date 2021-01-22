<?php

namespace Api\User\Repositories;

use Api\User\Models\Domain;
use Infrastructure\Database\Eloquent\Repository;

class DomainRepository extends Repository
{
    public function getModel()
    {
        return new Domain();
    }

    public function create(array $data)
    {
        $domain = $this->getModel();

        // ~ $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

        $domain->fill($data);
        $domain->save();

        return $domain;
    }

    public function update(User $user, array $data)
    {
        $user->fill($data);

        $user->save();

        return $user;
    }

    public function setGroups(User $user, array $addGroups, array $removeGroups = [])
    {
        $this->database->beginTransaction();

        try {
            if (count($removeGroups) > 0) {
                $query = $this->database->table($user->groups()->getTable());
                $query
                    ->where('user_id', $user->id)
                    ->whereIn('group_id', $removeGroups)
                    ->delete();
            }

            if (count($addGroups) > 0) {
                $query = $this->database->table($user->groups()->getTable());
                $query
                    ->insert(array_map(function ($groupId) use ($user) {
                        return [
                            'group_id' => $groupId,
                            'user_id' => $user->id
                        ];
                    }, $addGroups));
            }
        } catch (Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();
    }
}
