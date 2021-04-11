<?php

namespace Api\User\Testing\Feature;

use Api\User\Models\User;
use Api\Domain\Models\Domain;
use Api\Extension\Models\Extension;
use Api\Voicemail\Models\Voicemail;
use Infrastructure\Testing\TestCase;
use Infrastructure\Testing\UserTrait;
use Illuminate\Support\Facades\Notification;
use Api\User\Notifications\UserWasActivatedSelfNotification;
use Api\User\Notifications\UserWasCreatedSendVeirfyLinkNotification;

class UserControllerTest extends TestCase
{
    use UserTrait;

    public function testUserSignupSuccess()
    {
        $data = $this->testRequestFactoryService->makeUserSignupRequest(['noCache' => true]);
        list($response, $email) = $this->simulateDomainSignupAndActivate();
        $domain_name = $response->json('domain_name');
        $domainModel = Domain::where('domain_name', $domain_name)->first();
        $domain_uuid = $domainModel->domain_uuid;

        $nonExistingEmail = $this->prepareNonExistingEmailInDomain($domain_uuid);

        $data['user_email'] = $nonExistingEmail;
        $data['domain_name'] = $domain_name;

        $extension = Extension::where('domain_uuid', $domain_uuid)->max('extension');

        $data['extensions'] = [[
            'extension' => ++$extension, // Setting any non-exisiting number
            'password' => 'somePass',
            "voicemail_password" => "956"
        ]];

        $response = $this->json('post', route('fpbx.user.signup', $data));
        $response->assertStatus(201);

        $user = User::where(['domain_uuid' => $domain_uuid, 'user_email' => $data['user_email']])->first();
        Notification::assertSentTo($user, UserWasCreatedSendVeirfyLinkNotification::class);

        $domain = Domain::where('domain_name', $data['domain_name'])->first();

        $this->checkUserWithRelatedDataCreated($domain, $data);

        return $user;
    }

    public function atestUserActivateSuccess()
    {
        $model = $this->testUserSignupSuccess();

        $response = $this->json('get', route('fpbx.user.activate', ['hash' => $model->user_enabled]));
        $response->assertStatus(200);

        $response->assertJsonPath('message', __('User activated'));

        Notification::assertSentTo($model, UserWasActivatedSelfNotification::class);
    }

    public function atestActivateFailed()
    {
        $model = $this->testUserSignupSuccess();

        $hash = $model->user_enabled . '1';

        $response = $this->json('get', route('fpbx.user.activate', ['hash' => $hash]));
        $response->assertStatus(422);
        
        $hash = \Str::uuid()->toString();

        $response = $this->json('get', route('fpbx.user.activate', ['hash' => $hash]));
        $response->assertStatus(422);
    }

    public function test_ForgotPassword_Success()
    {
        // logic when test have to be passed 
        // (e.x. correct email + domain + user + evth exists etc ...)
    }

    public function test_ForgotPassword_Failed()
    {
        // all cases when test have to fail
    }


}
