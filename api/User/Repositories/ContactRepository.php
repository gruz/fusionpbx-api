<?php

namespace Api\User\Repositories;

use Api\User\Models\Contact;
use App\Database\Eloquent\Repository;

class ContactRepository extends Repository
{
    public function getModel()
    {
        return new Contact();
    }

    public function create(array $data)
    {
        $model = $this->getModel();

        // ~ $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

        $model->fill($data);
        $model->save();

        return $model;
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
