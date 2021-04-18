<?php

namespace Web\User\Controllers;

use Api\User\Services\UserPasswordService;
use Web\User\Requests\UserResetPasswordRequest;
use Web\User\Requests\UserUpdatePasswordRequest;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Schema(
 *  schema="WebUserController"
 * )
 */
class UserController extends BaseController
{
    /**
     * User get reset password action. Displays password reset form
     *
     @OA\Get(
        tags={"User"},
        path="/remind-password",
        x={
            "route-$path"="password.forgot",
            "route-$middlewares"="web",
        }
    )
     */
    public function getForgotPasswordForm()
    {
        return view('user.password.remind-password');
    }

    /**
     * User get reset password action. Displays password reset form
     *
     @OA\Get(
        tags={"User"},
        path="/reset-password",
        x={
            "route-$path"="password.reset",
            "route-$middlewares"="web",
        },
        @OA\Parameter(
            name="email",
            in="query",
            required=true,
            @OA\Schema(
                type="string",
                format="email",
                example="some@email.com",
            ),
        ),
        @OA\Parameter(
            name="domain_name",
            in="query",
            required=true,
            @OA\Schema(
                type="string",
                format="url",
                example="email.com",
            ),
        ),
        @OA\Parameter(
            name="token",
            in="query",
            required=true,
            @OA\Schema(
                type="string",
                example="71b4770ba36a165533fd75786a41793ea501f60f282f2464d27e16d2bb6bf516",
            ),
        ),
    )
     */
    public function getNewPasswordForm(UserResetPasswordRequest $request)
    {
        return view('user.password.reset-password', [
            'token' => $request->get('token'),
            'email' => $request->get('email'),
            'domain_name' => $request->get('domain_name')
        ]);
    }

    /**
     * User get reset password action. Displays password reset form
     *
     @OA\Get(
        tags={"User"},
        path="/invalid-link",
        x={
            "route-$path"="password.invalid-link",
            "route-$middlewares"="web",
        }
    )
     */
    public function invalidLink()
    {
        return view('user.password.invalid-link');
    }

    /**
     * User reset password after form submission
     *
     @OA\Post(
        tags={"User"},
        path="/password-update",
        x={
            "route-$path"="password.update",
            "route-$middlewares"="web",
        },
    )
     */
    public function passwordUpdate(UserUpdatePasswordRequest $request, UserPasswordService $userPasswordService )
    {
        $data = $request->only(
            'user_email',
            'password',
            'password_confirmation',
            'token',
            'domain_name',
        );

        $status = $userPasswordService->resetPassword($data);

        if ($status === null) {
            return back()->withErrors(['password' => __('Invalid data')]);
        }

        // return $this->response($this->passwordService->resetPassword($email));
        return view('user.password.updated');
    }
}
