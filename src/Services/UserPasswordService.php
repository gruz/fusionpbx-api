<?php

namespace Gruz\FPBX\Services;

use Illuminate\Support\Facades\Hash;
use Gruz\FPBX\Services\Fpbx\UserService;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Gruz\FPBX\Repositories\UserRepository;
use Gruz\FPBX\Repositories\DomainRepository;
use Gruz\FPBX\Models\PasswordReset as ModelsPasswordReset;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class UserPasswordService
{
    private $domainRepository;

    private $userService;

    public function __construct(
        UserRepository $userRepository,
        DomainRepository $domainRepository,
        UserService $userService
    ) {
        $this->userRepository = $userRepository;
        $this->domainRepository = $domainRepository;
        $this->userService = $userService;
    }

    /**
     * Method to generate token (that is necesary to reset password) and link that
     * will be sent to user via email to enable him to reset the password.
     *
     * @param $data Contains:
     *                  User email for which password needs to be reset.
     *                  Domain name to which user belongs.
     * @return array|mixed
     * @throws UnauthorizedHttpException
     * @throws AccessDeniedHttpException
     */


    public function generateResetToken($data)
    {
        /**
         * @var \Gruz\FPBX\Models\User[]
         */
        $users = $this->getUserCredentials($data);

        foreach ($users as $user) {
            /**
             * @var ModelsPasswordReset
             */
            $model = app(ModelsPasswordReset::class);

            $model->where([
                // ['email', $user->email],
                ['domain_name', $user->domain_name],
                ['username', $user->username],
            ])->delete();

            $model->email = $user->user_email;
            $model->domain_name = $user->domain_name;
            $model->username = $user->username;
            $model->token = mt_rand(100000, 999999);

            $model->save();

            $user->sendPasswordResetNotification($model->token);
        }

        return [
            'message' => __('Check your email'),
        ];
    }

    /**
     * Method to reset user password based on user credentials.
     */
    public function resetPassword($domain_name, $username, $password, $token)
    {
        $data = compact('domain_name', 'username', 'password', 'token');

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $data,
            function ($user) use ($password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    // 'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $status;
    }

    /**
     * Method to prepare user credential for password reset
     * If user user_email attribute is not set then we
     * should get user by domain and contact.
     *
     * @param $data Contains user email and domain name to which user belongs
     * @return null|\Gruz\FPBX\Models\User
     * @throws UnauthorizedHttpException
     * @throws AccessDeniedHttpException
     */
    public function getUserCredentials($data)
    {
        // domain_name is required filed so it cannot be empty

        $domain = $this->domainRepository
            ->getWhere('domain_name', $data['domain_name'])->first();

        if (is_null($domain)) {
            throw new  NotFoundHttpException(__(':entity not found', ['entity' => 'Domain']));
        }

        $attributes = [
            'domain_uuid' => $domain->domain_uuid,
            'user_email' => $data['user_email'],
        ];

        $users = $this->userService->getByAttributes($attributes);

        // if (!is_null($users)) {
        if ($users->count() > 0) {

            // if ($user->user_enabled !== 'true') {
            //     throw new AccessDeniedHttpException(__('User disabled'));
            // }

            return $users;
        }

        throw new  NotFoundHttpException(__(':entity not found', ['entity' => 'User']));
    }

    public function userSetPassword($data)
    {
        $userModel = $this->userService->getUserByUsernameAndDomain($data['username'], $data['domain_name']);

        if (!$userModel) {
            throw new  UnprocessableEntityHttpException(__('Password reset request invalid'));
        }

        $userModel->user_enabled = 'true';
        $userModel->password = Hash::make($data['password']);
        $userModel->save();

        return ['message' => __('Password updated')];
    }
}
