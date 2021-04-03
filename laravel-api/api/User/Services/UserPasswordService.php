<?php

namespace Api\User\Services;

use Illuminate\Foundation\Application;
use Infrastructure\Auth\Exceptions\InvalidCredentialsException;
use Api\User\Repositories\UserRepository;
use Api\Domain\Repositories\DomainRepository;
use Api\User\Repositories\ContactEmailRepository;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use Api\User\Exceptions\UserDisabledException;
use Api\User\Services\UserService;
use Api\Domain\Exceptions\DomainNotFoundException;

class UserPasswordService
{

    private $request;

    private $userRepository;

    private $domainRepository;

    private $userService;

    public function __construct(
      UserRepository $userRepository,
      DomainRepository $domainRepository,
      UserService $userService
    )
    {
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
     * @throws InvalidCredentialsException|UserDisabledException
     */
    public function generateResetToken($data)
    {
        $userCredentials = $this->getUserCredentials($data)->toArray();
        $status = Password::sendResetLink($userCredentials);

        return [
            'username' => $userCredentials['username'],
            'domain_uuid' => $userCredentials['domain_uuid'],
        ];
    }

    /**
     * Method to reset user password based on user credentials.
     *
     * @param $data Data from request
     * @return array|mixed
     * @throws InvalidCredentialsException|UserDisabledException
     */
    public function resetPassword($data)
    {
        $user = $this->getUserCredentials($email);
        $userCredentials = array_merge(
            $this->request->only(
                'user_email',
                'password',
                'password_confirmation',
                'token'
            ),
            $user->toArray()
        );

        Password::reset(
            $userCredentials,
            function ($user, $password) {
                $data = \encrypt_password_with_salt($password);
                $user->salt = $data['salt'];
                $user->fill(['password' => $data['password']]);
                $user->save();
                $user->setRememberToken(Str::random(60));
                event(new PasswordReset($user));
            }
        );

        return [
            'success' => 'Password has been successfully reset',
        ];
    }

    /**
     * Method to prepare user credential for password reset
     * If user user_email attribute is not set then we
     * should get user by domain and contact.
     *
     * @param $data Contains user email and domain name to which user belongs
     * @return null|\Api\User\Models\User
     * @throws InvalidCredentialsException|UserDisabledException
     */
    public function getUserCredentials($email, $domain_name = null)
    {
        // domain_name is required filed so it cannot be empty

        $domain = $this->domainRepository
                            ->getWhere('domain_name', $data['domain_name'])->first();

        // TODO:
        //      Needs to be refactored. Does we need it ?
        //      Maybe use better solution with filter...
        if (is_null($user) && !is_null($domain_name)) {
            $user = $this->getUserByEmailAndDomainName($email, $domain_name);
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

        throw new InvalidCredentialsException(__('User doesn\'t exists'));
    }

    /**
     * Method to get user by email (user_email attribute).
     *
     * @param $email User email
     * @return null|\Api\User\Models\User
     */
    public function getUserByEmail($email)
    {
        // Search by v_user table in user_email field
        $user = $this->userRepository
            ->getWhere('user_email', $email)
            ->first();

        return $user;
    }
}