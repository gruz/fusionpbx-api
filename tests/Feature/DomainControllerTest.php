<?php

namespace Tests\Feature;

use stdClass;
use Faker\Factory;
use Tests\TestCase;
use App\Models\User;
use App\Models\Domain;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Tests\Traits\UserTrait;
use App\Models\PostponedAction;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use App\Notifications\DomainSignupNotification;
use Illuminate\Notifications\AnonymousNotifiable;
use App\Notifications\DomainActivateActivatorNotification;
use App\Notifications\DomainActivateMainAdminNotification;
use App\Notifications\UserWasCreatedSendVeirfyLinkNotification;

class DomainControllerTest extends TestCase
{
    use UserTrait;

    public function testDomainSignupSuccess()
    {
        // $this->withoutExceptionHandling();
        // $this->expectException(\Exception::class);
        list($request, $response) = $this->simulateDomainSignup();

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

    public function testDomainActivateFailed()
    {
        // $this->withoutExceptionHandling();
        // list($request, $response) = $this->simulateDomainSignup();
        $this->simulateDomainSignup();

        $model = PostponedAction::last();
        $domain_name = Arr::get($model->request, 'domain_name');
        $email = Arr::get($model->request, 'users.0.user_email');

        // Bad hash in link
        $response = $this->json('get', route('fpbx.get.domain.activate', ['hash' => $model->hash . 'aa', 'email' => $email]));
        $response->assertStatus(422);
        $response->assertJsonPath('errors.0.title', __('Validation error'));
        $response->assertJsonPath('errors.0.detail', __('validation.uuid', ['attribute' => 'hash']));

        // Not hash exists in the table
        $response = $this->json('get', route('fpbx.get.domain.activate', ['hash' => Str::uuid()->toString(), 'email' => $email]));
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

    public function testActivateSuccessDomainEnabledOrDisabledByDefaultDependingOnConfig()
    {
        // $a = 'a';
        foreach ([false, true] as $hasDomainEnabledAttribute) {
            foreach ([false, true] as $key => $domain_enabled_after_activation) {
                config(['fpbx.domain.enabled' => $domain_enabled_after_activation]);
                $this->simulateDomainSignup(true);

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

    /**
     * Test using several request stored as json files. Means at least 2 request - much data and minimal data
     */
    public function testDomainActivateSuccessMany()
    {
        $requestFiles = Storage::files('swagger/domain/post/request/');
        foreach ($requestFiles as $jsonFile) {
            $data = json_decode(Storage::get($jsonFile), true);
            $faker = Factory::create(Factory::DEFAULT_LOCALE);
            $data['domain_name'] = $faker->domainName;
            $this->testDomainActivateSuccess($data);
        }
    }

    public function testDomainActivateSuccess($data = [])
    {
        if (!empty($data)) {
            $this->simulateDomainSignup(true, false, $data);
        } else {
            $this->simulateDomainSignup(true);
        }

        $model = PostponedAction::last();
        $domain_name = Arr::get($model->request, 'domain_name');
        $email = Arr::get($model->request, 'users.0.user_email');

        $data = collect($model->request);
        $requestUsers = collect($data->get('users'));

        $response = $this->json('get', route('fpbx.get.domain.activate', ['hash' => $model->hash, 'email' => $email]));
        $response->assertStatus(201);

        $domain = Domain::where('domain_name', $domain_name)->first();

        $this->checkDomainSettingsCreated($domain, $data);

        foreach ($requestUsers as $key => $userData) {
            $this->checkUserWithRelatedDataCreated($domain, $userData);
        }

        // $dialplan_dest_folder = config('app.fpath_document_root') . '/opt-laravel-api';
        // $this->assertDirectoryExists($dialplan_dest_folder);

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

    private function checkDomainSettingsCreated($domain, $data)
    {
        $requestSettings = collect($data->get('settings'));
        foreach ($requestSettings as $requestSetting) {
            $this->assertDatabaseHas('v_domain_settings', array_merge(
                ['domain_uuid' => $domain->domain_uuid],
                $requestSetting
            ));
        }
    }
}
