<?php

namespace Api\Domain\Testing\Feature;

use stdClass;
use Api\User\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Api\User\Models\Contact;
use Api\Domain\Models\Domain;
use Infrastructure\Testing\TestCase;
use Illuminate\Support\Facades\Notification;
use Api\PostponedAction\Models\PostponedAction;
use Illuminate\Notifications\AnonymousNotifiable;
use Api\Domain\Notifications\DomainSignupNotification;
use Api\Domain\Notifications\DomainActivateActivatorNotification;
use Api\Domain\Notifications\DomainActivateMainAdminNotification;
use Api\Extension\Models\Extension;
use Api\User\Notifications\UserWasCreatedSendVeirfyLinkNotification;
use Api\Voicemail\Models\Voicemail;

class DomainControllerTest extends TestCase
{
    public function testSignup_Success()
    {
        // $this->withoutExceptionHandling();
        // $this->expectException(\Exception::class);
        list($request, $response) = $this->simulateSignup();

        $this->assertDatabaseHas('postponed_actions', ['request->domain_name' => $request['domain_name']]);

        $users = collect(Arr::get($request, 'users'))->where('is_admin', true)->pluck('user_email')->toArray();

        // Assert a notification was sent to the given users...
        Notification::assertSentTo(
            new AnonymousNotifiable,
            DomainSignupNotification::class,
            function (DomainSignupNotification $notification, array $channels, AnonymousNotifiable $notifiable) use (&$users, $request) {
                if (!in_array($notifiable->routes['mail'], $users)) {
                    return false;
                }

                $email = $notifiable->routes['mail'];

                $url = route('fpbx.get.domain.activate', [
                    'hash' => PostponedAction::where('request->domain_name', $request['domain_name'])->first()->hash,
                    'email' => $email,
                ]);
                $recepient = new stdClass;
                $recepient->email = $email;
                $mail = $notification->toMail($recepient);
                $this->assertEquals($mail->actionUrl, $url);

                if (($key = array_search($email, $users)) !== false) {
                    unset($users[$key]);
                }

                return true;
            }
        );

        $this->assertEmpty($users, 'Not all users notified :' . print_r($users, true));

        $response->assertStatus(201);
    }

    public function testActivate_Failed()
    {
        // $this->withoutExceptionHandling();
        // list($request, $response) = $this->simulateSignup();
        $this->simulateSignup();

        $model = PostponedAction::last();
        $domain_name = Arr::get($model->request, 'domain_name');
        $email = Arr::get($model->request, 'users.0.user_email');

        // Bad hash in link
        $response = $this->json('get', route('fpbx.get.domain.activate', ['hash' => $model->hash . 'aa', 'email' => $email]));
        $response->assertStatus(422);
        $response->assertJsonPath('errors.0.title', __('Validation error'));
        $response->assertJsonPath('errors.0.detail', __('validation.uuid'));

        // Not hash exists in the table
        $response = $this->json('get', route('fpbx.get.domain.activate', ['hash' => Str::uuid(), 'email' => $email]));
        $response->assertStatus(422);
        $response->assertJsonPath('errors.0.title', __('Validation error'));
        $response->assertJsonPath('errors.0.detail', __('validation.exists', ['attribute' => 'hash', 'email' => $email]));

        // Trying to add already existing domain
        $model->setAttribute('request->domain_name', Domain::first()->getAttribute('domain_name'));
        $model->save();
        $response = $this->json('get', route('fpbx.get.domain.activate', ['hash' => $model->hash, 'email' => $email]));
        $response->assertStatus(422);
        $response->assertJsonPath('errors.0.title', __('Validation error'));
        $response->assertJsonPath('errors.0.detail', __('Domain already exists'));

        // Check link is expired

        // Restore domain from request
        $model->setAttribute('request->domain_name', $domain_name);

        // Simulate outdated link
        $model->created_at = $model->created_at->sub('1 year');
        $model->save();

        $response = $this->json('get', route('fpbx.get.domain.activate', ['hash' => $model->hash, 'email' => $email]));
        $response->assertStatus(422);
        $response->assertJsonPath('errors.0.title', __('Validation error'));
        $response->assertJsonPath('errors.0.detail', __('Domain activation link expired'));
    }

    public function testActivate_SuccessDomainEnabledOrDisabledByDefaultDependingOnConfig()
    {
        foreach ([false, true] as $hasDomainEnabledAttribute) {
            foreach ([false, true] as $key => $domain_enabled_after_activation) {
                config(['fpbx.domain.enabled' => $domain_enabled_after_activation]);
                $this->simulateSignup(true);

                /** @var PostponedAction */
                $model = PostponedAction::last();

                if ($hasDomainEnabledAttribute) {
                    $model->setAttribute('request->domain_enabled', $domain_enabled_after_activation);
                } else {
                    $json = $model->request;
                    Arr::forget($json, 'domain_enabled');
                    $model->request = $json;
                }
                $model->save();

                $domain_name = Arr::get($model->request, 'domain_name');
                $email = Arr::get($model->request, 'users.0.user_email');


                $response = $this->json('get', route('fpbx.get.domain.activate', ['hash' => $model->hash, 'email' => $email]));
                $response->assertStatus(201);


                $this->assertDatabaseHas('v_domains', ['domain_name' => $domain_name, 'domain_enabled' => $domain_enabled_after_activation]);
            }
        }
    }
    public function testActivate_Success()
    {
        $this->simulateSignup(true);

        $model = PostponedAction::last();
        $domain_name = Arr::get($model->request, 'domain_name');
        $email = Arr::get($model->request, 'users.0.user_email');

        $data = collect($model->request);
        $requestUsers = collect($data->get('users'));

        $response = $this->json('get', route('fpbx.get.domain.activate', ['hash' => $model->hash, 'email' => $email]));
        $response->assertStatus(201);

        $domain = Domain::where('domain_name', $domain_name)->first();

        $this->checkDomainCreated($domain, $data);

        foreach ($requestUsers as $key => $userData) {
            $this->checkContactsCreated($domain, $userData);
            $this->checkExtensionsCreated($domain, $userData, Extension::class);
            $this->checkExtensionsCreated($domain, $userData, Voicemail::class);
        }

        $dialplan_dest_folder = config('app.fpath_document_root') . '/opt-laravel-api';
        $this->assertDirectoryExists($dialplan_dest_folder);

        // If users created
        $users = User::where(['domain_uuid' => $domain->domain_uuid]);
        $this->assertEquals($requestUsers->count(), $users->count());

        $activatorUser = User::where(['domain_uuid' => $domain->domain_uuid, 'user_email' => $email])->first();

        Notification::assertNotSentTo($activatorUser, UserWasCreatedSendVeirfyLinkNotification::class);
        Notification::assertSentTo($activatorUser, DomainActivateActivatorNotification::class);

        foreach ($requestUsers as $user) {
            $user_email = $user['user_email'];
            if ($user_email !== $email) {
                $user = User::where(['domain_uuid' => $domain->domain_uuid, 'user_email' => $user_email])->first();
                Notification::assertSentTo($user, UserWasCreatedSendVeirfyLinkNotification::class);
            }
        }

        $mainAdminEmail = config('mail.from.address');

        Notification::assertSentTo(
            new AnonymousNotifiable,
            DomainActivateMainAdminNotification::class,
            function (DomainActivateMainAdminNotification $notification, array $channels, AnonymousNotifiable $notifiable) use (&$mainAdminEmail) {
                if ($notifiable->routes['mail'] !== $mainAdminEmail) {
                    return false;
                }
                return true;
            }
        );
    }

    public function test_Adding_new_domain_passes_with_several_admin_users_passes()
    {
    }

    public function test_Adding_existsing_domain_fails()
    {
        // $response = $this->get('/');

        // $response->assertStatus(200);

        // $this->assertTrue(true);
    }

    public function test_Adding_domain_with_no_or_bad_referral_code_fails()
    {
        return;
        $data = [
            'domain_name' => generate(),
            //
        ];

        $response = $this->post('/domain/signup', $data);

        $response->assertStatus(400);

        $data = [
            'domain_name' => generate(),
            'rereral_code' => 'bad',
            //
        ];

        $response = $this->post('/domain/signup', $data);

        $response->assertStatus(400);
    }

    private function checkDomainCreated($domain, $data)
    {
        $requestSettings = collect($data->get('settings'));
        foreach ($requestSettings as $requestSetting) {
            $this->assertDatabaseHas('v_domain_settings', array_merge(
                ['domain_uuid' => $domain->domain_uuid],
                $requestSetting
            ));
        }
    }

    private function checkContactsCreated($domain, $userData)
    {
        $contacts = Arr::get($userData, 'contacts');
        foreach ($contacts as $contactData) {
            $this->assertDatabaseHas('v_contacts', array_merge(
                ['domain_uuid' => $domain->domain_uuid],
                $contactData
            ));

            $contactsModel = Contact::where(array_merge(
                ['domain_uuid' => $domain->domain_uuid],
                $contactData
            ));

            foreach ($contactsModel as $contactModel) {
                $this->assertDatabaseHas('v_contact_users', [
                    'domain_uuid' => $domain->domain_uuid,
                    'user_uuid' => $contactModel->user_uuid,
                    'contact_uuid' => $contactModel->contact_uuid,
                ]);
            }
        }
    }

    private function checkExtensionsCreated($domain, $userData, $modelClass)
    {
        $extensions = Arr::get($userData, 'extensions', []);
        /**
         * @var \Infrastructure\Database\Eloquent\AbstractModel
         */
        $model = new $modelClass();
        $table = $model->getTable();
        foreach ($extensions as $extensionData) {
            if ('v_voicemails' === $table) {
                $extensionData['voicemail_id'] = $extensionData['extension'];
            }
            $tableColumns = $model->getTableColumnsInfo(true);
            $where =  [
                'domain_uuid' => $domain->domain_uuid
            ];
            foreach ($tableColumns as $columnName => $obj) {
                if (array_key_exists($columnName, $extensionData)) {
                    $where[$columnName] = $extensionData[$columnName];
                }
            }
            switch ($table) {
                case 'v_voicemails':
                    # code...
                    $this->assertNotEmpty($where['voicemail_password']);
                    break;
                default:
                    $this->assertNotEmpty($where['password']);
                    break;
            }
            $this->assertDatabaseHas($table, $where);
        }
    }

    public function testNoExtensionsPassedFails() {}
    public function testSimilarExtensionsForDifferentUsersInDomainFails() {}
}
