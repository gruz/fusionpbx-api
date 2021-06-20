<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Database\Eloquent\AbstractRepository;

class UserRepository extends AbstractRepository
{
    public function create(array $data, $options = [])
    {
        // $user = $this->getModel();
        if (empty($data['add_user'])) {
            // TODO get real main user here
            $data['add_user'] = 'admin';
        }

        // TODO. In FusionPBX it uses some format like Y-m-d H:i:s.uZ but directly in Postgre now() function.
        // So a date inserted by FusionPBX looks like 2017-05-01 09:46:30.945188-04
        // I don't know where it taks -04 (time zone), so I use more simple date format here. Maybe to fix later.
        // $data['add_date'] = date('Y-m-d H:i:s');
        $data['add_date'] = \Carbon\Carbon::now()->format(config('fpbx.time_format'));

        // ~ $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

        // ~ In FusionPBX the function is defined in fusionpbx/resources/functions.php
        // ~ $salt = uuid();
        // We will use a webpatser/laravel-uuid
        // $data['salt'] = Str::uuid();
        $data['user_enabled'] = Str::uuid()->toString();

        // ~ Normal laravel approach
        // $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

        // ~ FusionPBX approach
        // $data['password'] = md5($data['salt'] . $data['password']);

        // $passwordData = \encrypt_password_with_salt($data['password']);
        // $data['password'] = $passwordData['password'];
        // $data['salt'] = $passwordData['salt'];
        
        $data['password'] = Hash::make($data['password']);

        // ~ TODO Improve logic here, remove hardcoded
        // ~ $data['user_enabled'] = 'true';

        // ~ 'domain_uuid',  'username', 'password', 'salt', 'contact_uuid', 'user_enabled', 'add_user', 'add_date',


        // $user->domain_uuid = $data['domain_uuid'];
        // $user->contact_uuid = $data['contact_uuid'];

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

        return $model;
    }


    public function getUserByEmailAndDomain($user_email, $domain_name)
    {
        \DB::enableQueryLog();
        /**
         * @var User
         */
        $userModel = $this->getModel()->whereHas('domain', function ($q) use ($user_email, $domain_name) {
            $q->where('domain_name', '=', $domain_name);
        })->where('user_email', $user_email)->first();

        return $userModel;
    }

    public function getUserByUsernameAndDomain($username, $domain_name)
    {
        $userModel = $this->getModel()->whereHas('domain', function ($q) use ($username, $domain_name) {
            $q->where('domain_name', '=', $domain_name);
        })->where('username', $username)->first();

        return $userModel;
    }
}
