<?php

namespace Tests\Feature\UserController;

use Gruz\FPBX\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

class UserControllerForgotPasswordTest extends UserControllerSignupTest
{
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
            }
        );

        return [$data, $response, $token];
    }
 }
