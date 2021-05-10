<?php

namespace Web\User\Controllers;

use Illuminate\Http\Request;
use Api\Domain\Models\Domain;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Support\Facades\Route;
use Api\User\Services\UserPasswordService;
use Web\User\Requests\UserResetPasswordRequest;
use Web\User\Requests\UserUpdatePasswordRequest;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Schema(
 *  schema="Web/UserController"
 * )
 */
class UserController extends BaseController
{
    /**
     * User get reset password action. Displays password reset form
     *
     @OA\Get(
        tags={"User"},
        path="/user/register",
        x={
            "route-$path"="web.user.show.signup.form",
        }
    )
     */
    public function showSignipForm(MessageBag $messageBag, ViewErrorBag $viewErrorBag)
    {
        $domains = Domain::where('domain_enabled', true)->get()->toArray();
        return view('user.signup.form', ['domains' => $domains]);
        // // dd($domains);
        // $errors = $messageBag;
        // $errors->add('domain_name', 'Bad name');
        // $errors->add('email', 'godd email');

        // $viewErrorBag->put('default', $errors);

        // d($errors, $viewErrorBag);
        // return view('user.signup.form', ['domains' => $domains, 'errors' => $viewErrorBag]);
    }

    /**
     * User get reset password action. Displays password reset form
     *
     @OA\Post(
        tags={"User"},
        path="/user/register",
        x={
            "route-$path"="web.user.process.signup",
        }
    )
     */
    public function register(Request $request)
    {
        $request = \Illuminate\Support\Facades\Request::create(route('fpbx.user.signup'), 'POST', $request->all());

        $response = Route::dispatch($request);

        $validationErrors = json_decode($response->exception->getMessage(), true);
        $messageBag = new \Illuminate\Support\MessageBag;
        $messageBag->merge($validationErrors);
        return redirect()->back()->withErrors($messageBag->getMessages());


        d($response, $response->exception->getMessage());
        dd($response, json_decode($response->exception->getMessage()));
        return 'done';
    }

    /**
     * User get reset password action. Displays password reset form
     *
     @OA\Get(
        tags={"User"},
        path="/remind-password",
        x={
            "route-$path"="password.forgot"
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
            "route-$path"="password.reset"
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
            "route-$path"="password.invalid-link"
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
            "route-$path"="password.update"
        },
    )
     */
    public function passwordUpdate(UserUpdatePasswordRequest $request, UserPasswordService $userPasswordService)
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
