<?php

namespace Tests\Feature\UserController;

use App\Services\UserPasswordService;

class UserControllerLoginLogoutTest extends UserControllerSignupTest
{
    public function testUserLogin()
    {
        $user = $this->testUserActivateSuccess();

        // Successfull login {
        /**
         * @var UserPasswordService
         */
        $userPasswordService = app(UserPasswordService::class);
        $password = '.PanzerWagen14';
        $userPasswordService->userSetPassword($user, $password);

        $data = [
            'domain_name' => $user->domain_name,
            'username' => $user->username,
            'password' => $password,
        ];
        $response = $this->json('post', route('fpbx.post.user.login'), $data);

        $response->assertStatus(200);
        $response->assertJsonStructure(['access_token']);
        // Successfull login }

        // Bad password {
        $data2 = $data;
        $data2['password'] = $this->faker->password();
        $response = $this->json('post', route('fpbx.post.user.login'), $data2);
        $response->assertStatus(401);
        $response->assertJsonFragment(["message" => "Invalid credentials."]);

        $data2 = $data;
        $data2['username'] = $this->faker->userName;
        $response = $this->json('post', route('fpbx.post.user.login'), $data2);
        $response->assertStatus(401);
        $response->assertJsonFragment(["message" => "Invalid credentials."]);
        // Bad password }
    }

    public function testUserLogout()
    {
    }
}
