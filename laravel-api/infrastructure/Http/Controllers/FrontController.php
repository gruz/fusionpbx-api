<?php

namespace Infrastructure\Http\Controllers;

use Api\User\Models\User;
use Api\User\Models\Contact;
use Illuminate\Http\Request;
use Api\Domain\Models\Domain;
use Api\Settings\Models\Setting;
use Api\Extension\Models\Extension;
use Api\Voicemail\Models\Voicemail;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Routing\Controller as BaseController;

class FrontController extends BaseController
{
    public function test(Request $request)
    {
        if (!config('app.debug')) {
            return;
        }

        $this->testRequestFactoryService = app(\Infrastructure\Services\TestRequestFactoryService::class);
        $data = $this->testRequestFactoryService->makeDomainRequest([
            'adminIsPresent' => false,
        ]);
        // \Illuminate\Support\Arr::set($data, 'users.0.user_email', 'a@a.com');
        // \Illuminate\Support\Arr::set($data, 'users.1.user_email', 'a@a.com');

        dd($data);
        $settings = Setting::factory(2)->make()->toArray();

        $is_admin = new Sequence(
            ['is_admin' => true],
            ['is_admin' => false],
        );

        $users = User::factory(3)
                ->state($is_admin)
                ->state(function (array $attributes) {return [
                    'contacts' => Contact::factory(2)
                        ->make()
                        ->makeVisible('password')
                        ->toArray(),
                ];})
                ->state(function (array $attributes) {
                    $extensions = [];
                    for ($i=0; $i < rand(1,4); $i++) { 
                        $extension = Extension::factory(1)->make()->first()->toArray();
                        $voicemail = Voicemail::factory(1)->make()->first()->toArray();
    
                        $extensions[] = array_merge(
                            $extension,
                            $voicemail
                        );
                    }
                    // dd($extension, $voicemail);
                    return  [
                    'extensions' => $extensions];
                })
                ->make()
                ->makeVisible('password')
                ->toArray();

        $model = Domain::factory()->make([
            'settings' => $settings,
            'users' => $users,
        ]);
        // $model = Setting::factory()->make();
        dd($model->toArray());


        \DB::enableQueryLog(); // Enable query log
        $items = \Api\Extension\Models\Extension::with('extension_users.permissions')
            ->where('extension_uuid', '63045580-4ce3-11eb-b21b-47744eb6524b')
            ->get()
            ->toArray();
        \dd($items, \DB::getQueryLog());

        phpinfo();
        try {

            \DB::enableQueryLog(); // Enable query log
            $user = User::with('groups')
                ->get()->toArray();

            \dd($user, \DB::getQueryLog());
        } catch (\Exception $e) {
            dd($e);
        }
    }
}
