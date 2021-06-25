<?php

namespace App\Services;

use App\Models\User;
use League\Flysystem\Util;
use Illuminate\Support\Arr;
use App\Models\Contact;
use App\Models\Domain;
use App\Models\Setting;
use App\Models\Extension;
use App\Models\UserSetting;
use App\Models\Voicemail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Factories\Sequence;

class TestRequestFactoryService
{
    public function makeDomainSignupRequest($params = [])
    {
        $noCache = Arr::get($params, 'noCache');
        $skey = 'testing/' . Util::normalizePath(__FUNCTION__ . serialize(func_get_args()));
        // dd($skey);

        if (!$noCache && $data = Cache::store('file')->get($skey)) {
            $data = unserialize($data);
            // Don't delete, for getting JSON requests as example
            \Illuminate\Support\Facades\Storage::put('domain.signup.request.json', json_encode($data, JSON_PRETTY_PRINT));
            return $data;
        }

        $settings = Setting::factory(2)->make()->toArray();

        $users = $this->makeUserSignupRequest([
            'numberOfUsers' => 3,
            'adminIsPresent' => true,
        ]);

        $model = Domain::factory()->make([
            'is_subdomain' => false,
            'settings' => $settings,
            'users' => $users,
        ]);

        $return =  $model->toArray();

        if (!$noCache) {
            Cache::store('file')->set($skey, serialize($return));
        }
        // Don't delete, for getting JSON requests as example
        \Illuminate\Support\Facades\Storage::put('domain.signup.request.json', json_encode($return, JSON_PRETTY_PRINT));

        return $return;
    }

    public function makeUserSignupRequest($params = [])
    {
        $noCache = Arr::get($params, 'noCache');
        $numberOfUsers = Arr::get($params, 'numberOfUsers', 1);
        $addDomainName = Arr::get($params, 'addDomainName', false);
        $domain_name = Arr::get($params, 'domain_name', null);
        $addResellerCode = Arr::get($params, 'addResellerCode', null);

        $skey = 'testing/' . Util::normalizePath(__FUNCTION__ . serialize(func_get_args()));

        if (!$noCache && $data = Cache::store('file')->get($skey)) {
            $data = unserialize($data);
            // Don't delete, for getting JSON requests as example
            \Illuminate\Support\Facades\Storage::put('user.signup.request.json', json_encode($data, JSON_PRETTY_PRINT));
            return $data;
        }

        $adminIsPresent = Arr::get($params, 'adminIsPresent', null);

        switch (true) {
            case ($adminIsPresent === true):
                $is_admin = new Sequence(
                    ['is_admin' => true],
                    ['is_admin' => false],
                );
                break;
            case ($adminIsPresent === false):
                $is_admin = ['is_admin' => false];
                break;
            default:
                $is_admin = [];
                break;
        }

        $users = User::factory($numberOfUsers)
            ->state($is_admin)
            ->state(function (array $attributes) {
                return [
                    'contacts' => Contact::factory(2)
                        ->make()
                        ->makeVisible('password')
                        ->toArray(),
                ];
            })
            ->state(function (array $attributes) {
                $extensions = [];
                for ($i = 0; $i < rand(1, 4); $i++) {
                    $extension = Extension::factory(1)->make()->makeVisible('password')->first()->toArray();
                    $voicemail = Voicemail::factory(1)->make()->first()->toArray();

                    $extensions[] = array_merge(
                        $extension,
                        $voicemail
                    );
                }
                // dd($extension, $voicemail);
                return  [
                    'extensions' => $extensions
                ];
            });

        if ($addDomainName && !empty($domain_name)) {
            $users = $users->state(['domain_name' => $domain_name]);
        }

        if ($addResellerCode) {
            $users = $users->state(function (array $attributes) {
                return [
                    'user_settings' => UserSetting::factory(1)
                        ->make()
                        // ->makeVisible('password')
                        ->toArray(),
                ];
            });
        }

        $users = $users->make()
            ->makeVisible('user_email')
            ->makeVisible('password');

        if (empty($is_admin)) {
            $users->makeHidden('is_admin');
        }

        if (!$addDomainName) {
            $users->makeHidden('domain_name');
        }

        if (1 === $numberOfUsers) {
            $return =  $users[0]->toArray();
        } else {
            $return =  $users->toArray();
        }

        if (!$noCache) {
            Cache::store('file')->set($skey, serialize($return));
        }
        // Don't delete, for getting JSON requests as example
        \Illuminate\Support\Facades\Storage::put('user.signup.request.json', json_encode($return, JSON_PRETTY_PRINT));

        return $return;
    }
}
