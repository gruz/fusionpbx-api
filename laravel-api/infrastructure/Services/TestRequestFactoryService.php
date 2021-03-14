<?php

namespace Infrastructure\Services;

use Api\User\Models\User;
use League\Flysystem\Util;
use Illuminate\Support\Arr;
use Api\User\Models\Contact;
use Api\Domain\Models\Domain;
use Api\Settings\Models\Setting;
use Api\Extension\Models\Extension;
use Api\Voicemail\Models\Voicemail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Factories\Sequence;

class TestRequestFactoryService
{
    public function makeDomainRequest($params = [])
    {
        $skey = 'testing/' . Util::normalizePath(__FUNCTION__ . serialize(func_get_args()));
        // dd($skey);

        if ($data = Cache::store('file')->get($skey)) {
            $data = unserialize($data);
            // Don't delete, for getting JSON requests as example
            \Illuminate\Support\Facades\Storage::put('request.json', json_encode($data, JSON_PRETTY_PRINT));
            return $data;
        }

        $settings = Setting::factory(2)->make()->toArray();

        $adminIsPresent = Arr::get($params, 'adminIsPresent', true);
        if ($adminIsPresent) {
            $is_admin = new Sequence(
                ['is_admin' => true],
                ['is_admin' => false],
            );
        } else {
            $is_admin = ['is_admin' => false];
        }

        $users = User::factory(3)
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
                    $extension = Extension::factory(1)->make()->first()->toArray();
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
            })
            ->make()
            ->makeVisible('user_email')
            ->makeVisible('password')
            ->toArray();

        $model = Domain::factory()->make([
            'is_subdomain' => false,
            'settings' => $settings,
            'users' => $users,
        ]);

        $return =  $model->toArray();

        Cache::store('file')->set($skey, serialize($return));
        // Don't delete, for getting JSON requests as example
        \Illuminate\Support\Facades\Storage::put('request.json', json_encode($return, JSON_PRETTY_PRINT));

        return $return;
    }
}
