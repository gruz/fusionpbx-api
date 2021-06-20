<?php

namespace Api\User\Testing\Feature;

use Api\User\Models\User;
use Illuminate\Support\Arr;
use Api\Domain\Models\Domain;
use App\Testing\TestCase;
use App\Testing\UserTrait;
use Api\Settings\Models\DefaultSetting;
use Api\Extension\Services\ExtensionService;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\ResetPassword;
use Api\User\Notifications\UserWasActivatedSelfNotification;
use Api\User\Notifications\UserWasCreatedSendVeirfyLinkNotification;

class UserControllerTest extends TestCase
{
    use UserTrait;

    public function testUserSignupSuccess($resellerCodeRequired = false)
    {
        config(['fpbx.resellerCodeRequired' => $resellerCodeRequired]);

        $data = $this->testRequestFactoryService->makeUserSignupRequest(['noCache' => true]);
        list($response, $email) = $this->simulateDomainSignupAndActivate();
        $domain_name = $response->json('domain_name');
        $domainModel = Domain::where('domain_name', $domain_name)->first();
        $domain_uuid = $domainModel->domain_uuid;

        $nonExistingEmail = $this->prepareNonExistingEmailInDomain($domain_uuid);

        $data['user_email'] = $nonExistingEmail;
        $data['domain_name'] = $domain_name;

        $extension = app(ExtensionService::class)->getNewExtension($this->domain_uuid);

        $data['extensions'] = [[
            'extension' => $extension, // Setting any non-exisiting number
            'password' => 'somePass',
            "voicemail_password" => "956"
        ]];

        if ($resellerCodeRequired) {
            $reseller_code = DefaultSetting::where([
                ['default_setting_category', 'billing'],
                ['default_setting_subcategory', 'reseller_code'],
                ['default_setting_enabled', true],
            ])->first();

            if (empty($reseller_code)) {
                $reseller_code = $this->createResellerCode();
            } else {
                $reseller_code = $reseller_code->getAttribute('default_setting_value');
            }
            $data['reseller_reference_code'] = $reseller_code;
        }

        $response = $this->json('post', route('fpbx.user.signup', $data));
        $response->assertStatus(201);

        $user = User::where(['domain_uuid' => $domain_uuid, 'user_email' => $data['user_email']])->first();
        Notification::assertSentTo($user, UserWasCreatedSendVeirfyLinkNotification::class);

        $domain = Domain::where('domain_name', $data['domain_name'])->first();

        if ($resellerCodeRequired) {
            $userSettings = Arr::get($data, 'user_settings', []);
            $userSettings[] = [
                "user_setting_category" => "payment",
                "user_setting_subcategory" => "reseller_code",
                "user_setting_value" => $reseller_code,
            ];
            Arr::set($data, 'user_settings', $userSettings);
        }

        $this->checkUserWithRelatedDataCreated($domain, $data);

        return $user;
    }

    public function testUserSignupSuccessWithResellerCodeRequired()
    {
        $this->testUserSignupSuccess(true);
    }

    public function testUserActivateSuccess()
    {
        $userModel = $this->testUserSignupSuccess();

        $response = $this->json('get', route('fpbx.user.activate', ['hash' => $userModel->user_enabled]));
        $response->assertStatus(200);

        $response->assertJsonPath('message', __('User activated'));

        $userModel->refresh();
        $response->assertJsonPath('user', $userModel->toArray());

        Notification::assertSentTo($userModel, UserWasActivatedSelfNotification::class);

        return $userModel;
    }

    public function testActivateFailed()
    {
        $userModel = $this->testUserSignupSuccess();

        $hash = $userModel->user_enabled . '1';

        $response = $this->json('get', route('fpbx.user.activate', ['hash' => $hash]));
        $response->assertStatus(422);

        $hash = \Str::uuid()->toString();

        $response = $this->json('get', route('fpbx.user.activate', ['hash' => $hash]));
        $response->assertStatus(422);

        // Maybe later think of behavior if domain is disabled. Should we allow to activate user?
        // Should the activation link work later when currentry disabled domain is activated?
        // $domainModel = $userModel->domain;
        // $domainModel->domain_enabled = false;
        // $domainModel->save();
    }

    public function test_ForgotPassword_Success()
    {
        $userModel = $this->testUserActivateSuccess();
        $data = [
            'domain_name' => $userModel->domain->getAttribute('domain_name'),
            'user_email' => $userModel->user_email,
        ];

        $response = $this->json('post', route('fpbx.user.forgot-password'), $data);

        $response->assertStatus(200);
        $response->assertJson([
            "username" => $userModel->username,
            'domain_uuid' => $userModel->getAttribute('domain_uuid'),
        ]);

        // Notification::assertSentTo($userModel, ResetPassword::class);

        $token = '';

        Notification::assertSentTo(
            $userModel,
            ResetPassword::class,
            function ($notification, $channels) use (&$token) {
                $token = $notification->token;

                return true;
            });

        return [$data, $response, $token];
    }
}
