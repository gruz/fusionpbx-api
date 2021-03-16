<?php

namespace Api\User\Repositories;

use Api\User\Models\User;
use Illuminate\Support\Str;
use Infrastructure\Database\Eloquent\AbstractRepository;

class UserRepository extends AbstractRepository
{
    public function create(array $data)
    {
        if (empty($data['add_user'])) {
            // TODO get real main user here
            $data['add_user'] = 'admin';
        }

        // TODO. In FusionPBX it uses some format like Y-m-d H:i:s.uZ but directly in Postgre now() function.
        // So a date inserted by FusionPBX looks like 2017-05-01 09:46:30.945188-04
        // I don't know where it taks -04 (time zone), so I use more simple date format here. Maybe to fix later.
        $data['add_date'] = date('Y-m-d H:i:s');

        // ~ $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

        // ~ In FusionPBX the function is defined in fusionpbx/resources/functions.php
        // ~ $salt = uuid();
        // We will use a webpatser/laravel-uuid
        $data['salt'] = Str::uuid();
        $data['user_enabled'] = Str::uuid();

        // ~ Normal laravel approach
        // $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

        // ~ FusionPBX approach
        $data['password'] = md5($data['salt'] . $data['password']);

        // ~ TODO Improve logic here, remove hardcoded
        // ~ $data['user_enabled'] = 'true';

        // ~ 'domain_uuid',  'username', 'password', 'salt', 'contact_uuid', 'user_enabled', 'add_user', 'add_date',


        // $user->domain_uuid = $data['domain_uuid'];
        // $user->contact_uuid = $data['contact_uuid'];
        // $user->salt = $data['salt'];

        // if (!empty($data['user_status']) && !is_null($data['user_status'])) {
        //     $user->user_status = $data['user_status'];
        // }

        // $user->fill($data);
        // $user->save();

        $model = $this->getModel();

        $availableColumns = $model->getTableColumnNames(true);

        foreach ($data as $key => $value) {
            if (in_array($key, $availableColumns)) {
                $model->$key = $value;
            }
        }

        $model->save();

        // $model = parent::create($data);
        return $model;
    }

    public function setGroups(User $user, array $addGroups, array $removeGroups = [])
    {
        $this->database->beginTransaction();

        try {
            // TODO Check if Remove here works.
            if (count($removeGroups) > 0) {
                $query = $this->database->table($user->groups()->getTable());
                $query
                    ->where('user_uuid', $user->user_uuid)
                    ->where('domain_uuid', $user->domain_uuid)
                    ->whereIn('group_uuid', $removeGroups)
                    ->delete();
            }

            if (count($addGroups) > 0) {
                $query = $this->database->table($user->groups()->getTable());
                $query
                    ->insert(array_map(function ($groupName, $groupId) use ($user) {
                        return [
                            'user_group_uuid' => Str::uuid(),
                            'domain_uuid' => $user->domain_uuid,
                            'group_uuid' => $groupId,
                            'group_name' => $groupName,
                            'user_uuid' => $user->user_uuid
                        ];
                    }, $addGroups, array_keys($addGroups)));
            }
        } catch (\Exception $e) {
            $this->database->rollBack();

            throw $e;
        }

        $this->database->commit();
    }
}
