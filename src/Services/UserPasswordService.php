<?php

namespace Gruz\FPBX\Services;

use Gruz\FPBX\Models\User;
use Gruz\FPBX\Services\Fpbx\UserService;
use Gruz\FPBX\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Gruz\FPBX\Repositories\DomainRepository;
use Illuminate\Support\Facades\Password;
use Gruz\FPBX\Exceptions\UserDisabledException;
use Illuminate\Auth\Events\PasswordReset;
use Gruz\FPBX\Exceptions\DomainNotFoundException;
use Illuminate\Contracts\Auth\PasswordBroker;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class UserPasswordService
{

    private $userRepository;

    private $domainRepository;

    private $userService;

    public function __construct(
        UserRepository $userRepository,
        DomainRepository $domainRepository,
        UserService $userService,
        PasswordBroker $passwordBroker
    ) {
        $this->userRepository = $userRepository;
        $this->domainRepository = $domainRepository;
        $this->userService = $userService;
        $this->passwordBroker = $passwordBroker;
    }

    /**
     * Method to generate token (that is necesary to reset password) and link that
     * will be sent to user via email to enable him to reset the password.
     *
     * @param $data Contains:
     *                  User email for which password needs to be reset.
     *                  Domain name to which user belongs.
     * @return array|mixed
     * @throws UnauthorizedHttpException|UserDisabledException
     */
    public function generateResetToken($data)
    {
        $userCredentials = $this->getUserCredentials($data)->toArray();
        Password::sendResetLink($userCredentials);

        return [
            'username' => $userCredentials['username'],
            'domain_uuid' => $userCredentials['domain_uuid'],
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
     * @throws UnauthorizedHttpException|UserDisabledException
     */
    public function getUserCredentials($data)
    {
        // domain_name is required filed so it cannot be empty

        $domain = $this->domainRepository
            ->getWhere('domain_name', $data['domain_name'])->first();

        if (is_null($domain)) {
            throw new DomainNotFoundException();
        }

        $attributes = [
            'domain_uuid' => $domain->domain_uuid,
            'user_email' => $data['user_email'],
        ];

        $user = $this->userService->getByAttributes($attributes)->first();

        if (!is_null($user)) {

            if ($user->user_enabled != 'true') {
                throw new UserDisabledException();
            }

            return $user;
        }


        throw new UnauthorizedHttpException('Basic', __('User doesn\'t exists'));
    }

    /**
     * Method to get user by email (user_email attribute).
     *
     * @param $email User email
     * @return null|\Gruz\FPBX\Models\User
     */
    public function getUserByEmail($email)
    {
        // Search by v_user table in user_email field
        $user = $this->userRepository
            ->getWhere('user_email', $email)
            ->first();

        return $user;
    }

    public function userSetPassword(User $user, $password)
    {
        $data = [
            'domain_uuid' => $user->getAttribute('domain_uuid'),
            'user_email' => $user->user_email,
        ];

        $status = $this->passwordBroker->sendResetLink($data, function($user, $token) {
            $this->ttoken = $token;
        });

        $status = $this->resetPassword($user->domain_name, $user->username, $password, $this->ttoken);

        return $status;
    }
}
