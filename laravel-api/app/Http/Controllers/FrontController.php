<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Arr;
use App\Models\Contact;
use Illuminate\Http\Request;
use App\Models\Domain;
use App\Models\Setting;
use App\Models\Extension;
use App\Models\Voicemail;
use App\Services\Fpbx\DomainService;
use Illuminate\Support\Facades\Notification;
use App\Services\FreeSwicthHookService;
use App\Repositories\ExtensionRepository;
use Illuminate\Database\Eloquent\Factories\Sequence;

class FrontController extends AbstractController
{
    public function refreshCaptcha(Request $request)
    {
        $type = $request->get('type', '');
        return response()->json(['captcha' => captcha_img($type)]);
    }

    public function test(Request $request)
    {
        if (!config('app.debug')) {
            return;
        }
        // $user = User::where('user_uuid', '39583b9c-91aa-46aa-bcc3-5871ce1ad927')->first();
        // dd($user->getCGRTBalanceAttribute());
        // $account_code = 'aaa';

        // $data = [
        //     "user_uuid" => $user->user_uuid,
        //     "domain_uuid" => $user->domain_uuid,
        //     "user_setting_category" => "payment",
        //     "user_setting_subcategory" => "account_code",
        //     "user_setting_name" => "text",
        //     "user_setting_value" => $account_code,
        //     "user_setting_order" => 0,
        //     "user_setting_enabled" => true,
        //     "user_setting_description" => 'CGRT account code',
        // ];
        // $user_setting = new UserSetting();
        // $user_setting->fillable[] = 'domain_uuid';
        // $user_setting->fillable[] = 'user_uuid';
        // $user_setting->fill($data);
        // // foreach ($data as $key => $value) {
        // //     $user_setting->$key = $value;
        // // }
        // $user_setting->save();
        // d($user->user_settings());
        // dd($user->user_settings());
        // $user->user_settings()->create($data);


        // /**
        //  * @var CGRTService
        //  */
        // $client = app(CGRTService::class);
        // // $r = $client->getReferenceCodes();
        // $user = User::where('user_uuid', '27d11471-1643-4905-a24c-0fdb2a597d11')->first();
        // $client->addClient($user);
        // $r = $client->getTenants();
        // dd($r);
        // exit;

        // Route::get('/test-mail', function (){
            $user = User::where('username', 'A05lyson.dietrich.howe.com')->first();
            $n = new \App\Notifications\UserWasActivatedSelfNotification($user);
            return $n->toMail($user);
            dd($user, $n->toMail($user));
            Notification::route('mail', 'some@s')->notify(new UserWasActivatedSelfNotification());
            // return 'Sent';
        // });


        $s = app(DomainService::class);
        dd($s->getSystemDomain());
        /**
         * @var ExtensionRepository
         */
        $extensionRepository = app(ExtensionRepository::class);

        // \DB::enableQueryLog();
        $extension = $extensionRepository->getNewExtension('ab14e310-c12e-4b08-b95c-0e7239a1f623');
        // dd($extension, \DB::getQueryLog());

        return $extension;


        dd(config('domain_enabled_field_type'));

        $s = new FreeSwicthHookService;
        $response = $s->reload();
        dd($response);

        \DB::enableQueryLog();
        $user = User::where('username', 'mplotnikov01')->first();
        dd(
            $user->domain_name,
            $user->getDomainAdmins(),
        );

        $this->testRequestFactoryService = app(\App\Services\TestRequestFactoryService::class);

        $data = $this->testRequestFactoryService->makeUserSignupRequest([
            'noCache' => true,
            'addDomainName' => true,
        ]);

        dd($data);

        $ral = new User;
        // d($ral->groups(), $ral->groups()->getPivotColumns(), $ral->contacts()->getPivotColumns());

        d(
            $ral->extensions(),
            $ral->extensions()->getPivotColumns(),
            $ral->extensions()->getPivotClass(),
            $ral->extensions()->getQualifiedForeignPivotKeyName(),
            $ral->extensions()->getExistenceCompareKey(),
            $ral->extensions()->getForeignPivotKeyName(),
            $ral->extensions()->getQualifiedParentKeyName(),
            $ral->extensions()->getRelatedKeyName(),
            $ral->extensions()->getQualifiedRelatedKeyName(),
            $ral->extensions()->getQualifiedRelatedPivotKeyName(),
            $ral->extensions()->getTable()


        );
        exit;

        $user = \App\Models\User::
            // where('user_uuid', '<>', '')
            skip(1)->first();
        $notification = new \App\Notifications\UserWasCreatedSendVeirfyLinkNotification($user);
        \Illuminate\Support\Facades\Notification::send($user, $notification);
        return 'Sent';

        \DB::enableQueryLog();
        $model = new \App\Models\PostponedAction;
        // $model = $model->first();
        // $model = $model->where('request->users', '@>', 'alyson.dietrich@howe.com')
        $model = $model->whereJsonContains('request->users', [
            ["user_email" => "alyson.dietrich@howe.com"]
        ])
            ->first();

        dd(optional($model)->toArray(), \DB::getQueryLog());


        // $model->hash = Str::uuid();
        // $model->request = $request->toArray();
        // $model->save();

        // $model->setAttribute('request->dddd', 'aaaa');
        -
        // $field = $model->request;
        // $field['aaa'] = 'bbb';
        // $model->request = $field;
        dd($model->request);

        $model->save();

        // $model2 = \App\Models\PostponedAction::where('request->domain_name', 'aaa.com')->firstOrFail();
        dd($model);

        $expireDate = $model->created_at->add();

        $this->testRequestFactoryService = app(\App\Services\TestRequestFactoryService::class);

        $data = $this->testRequestFactoryService->makeDomainSignupRequest();

        $userData = Arr::get($data, 'users.0');
        /**
         * @var User
         */
        $user = new User;

        // $user->username = 'aaa';
        // $user->save();
        $user->create($userData);

        dd($user, $user->getFillable(), $user->getGuarded());

        return;
        $data = $this->testRequestFactoryService->makeDomainSignupRequest([
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
            ->makeVisible('password')
            ->toArray();

        $model = Domain::factory()->make([
            'settings' => $settings,
            'users' => $users,
        ]);
        // $model = Setting::factory()->make();
        dd($model->toArray());


        \DB::enableQueryLog(); // Enable query log
        $items = \App\Models\Extension::with('extension_users.permissions')
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
