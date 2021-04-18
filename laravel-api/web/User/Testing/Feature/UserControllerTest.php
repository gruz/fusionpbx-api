<?php

namespace Web\User\Testing\Feature;

use Api\User\Services\UserService;

class UserControllerTest extends \Api\User\Testing\Feature\UserControllerTest
{
    public function testGetNewPasswordFormSuccess()
    {
        $url = url(route('password.forgot'));
        $response = $this->json('get', $url);
        // $response->assertViewHas('Reset Password');
        $response->assertViewIs('user.password.remind-password');
        // $response->assertSee('<input id="domain_name" type="text"', false);
        // $response->assertSee('<input id="user_email" type="email"', false);
    }

    public function testResetPasswordShowFormSuccess()
    {
        list($data, $response, $token) = $this->test_ForgotPassword_Success();
        $resetRecord = \DB::table(('password_resets'))->where([
            ['email', $data['user_email']],
            ['domain_name', $data['domain_name']],
        ])->first();


        $url = url(route('password.reset', [
            'token' => $token,
            'email' => $resetRecord->email,
            'domain_name' => $resetRecord->domain_name,
        ], false));
        $response = $this->json('get', $url);

        $response->assertViewIs('user.password.reset-password');

        $response->assertSee('Reset Password');
        $response->assertSee($token);
        $response->assertSee($resetRecord->email);
   }

   public function testPasswordUpdateSuccess()
   {
       list($data, $response, $token) = $this->test_ForgotPassword_Success();
    //    dd($data, $response, $token);
    //    $newPassword = $this->faker->password(12);
       $newPassword = '.Apantera123';
       $postData = [
           'user_email' => $data['user_email'],
           'password' => $newPassword,
           'password_confirmation' => $newPassword,
           'token' => $token,
           'domain_name' => $data['domain_name'],
       ];

       $response = $this->post(route('password.update'), $postData);

       $response->assertStatus(200);
       $response->assertViewIs('user.password.updated');

       /**
        * @var UserService
        */
       $userService = app(UserService::class);
       $userModel = $userService->getUserByEmailAndDomain($data['user_email'], $data['domain_name']);

       $postData = [
           'username' => $userModel->username,
           'domain_name' => $data['domain_name'],
           'password' => $newPassword,
       ];

        $response = $this->post(route('login'), $postData);

        $response->assertStatus(200);
        $response->assertJson([
            "user_uuid" => $userModel->user_uuid,
            "username" => $userModel->username,
            'domain_uuid' => $userModel->getAttribute('domain_uuid'),
        ]);

        $res_array = (array)json_decode($response->content());
        $this->assertArrayHasKey('access_token', $res_array);
        $this->assertArrayHasKey('expires_in', $res_array);
  }
}
