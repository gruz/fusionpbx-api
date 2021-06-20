<?php

namespace Api\Domain\Repositories;

use App\Database\Eloquent\AbstractRepository;

class DomainRepository extends AbstractRepository
{

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
