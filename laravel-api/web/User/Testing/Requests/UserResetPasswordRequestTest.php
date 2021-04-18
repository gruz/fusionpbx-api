<?php

namespace Web\User\Testing\Requests;

use Infrastructure\Testing\UserTrait;
use Api\User\Testing\Feature\UserControllerTest;

class UserResetPasswordRequestTest extends UserControllerTest
{
    use UserTrait;

    public function testResetPasswordLinkExpired()
    {
        list($data, $response, $token) = $this->test_ForgotPassword_Success();
        $resetRecord = \DB::table(('password_resets'))->where([
            ['email', $data['user_email']],
            ['domain_name', $data['domain_name']],
        ])->first();

        $url = url(route('password.reset', [
            'token' => $token,
            'email' => $resetRecord->email . '.com',
            'domain_name' => $resetRecord->domain_name,
        ], false));

        $response = $this->json('get', $url);
        $response->assertStatus(422);

        $url = url(route('password.reset', [
            'token' => $token.'1',
            'email' => $resetRecord->email,
            'domain_name' => $resetRecord->domain_name,
        ], false));

        $response = $this->json('get', $url);
        $response->assertStatus(422);

        $url = url(route('password.reset', [
            'token' => $token,
            'email' => $resetRecord->email,
            'domain_name' => $resetRecord->domain_name.'.com',
        ], false));

        $response = $this->json('get', $url);
        $response->assertStatus(422);
   }
}
