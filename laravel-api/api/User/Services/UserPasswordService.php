<?php

namespace Api\User\Services;

use Illuminate\Foundation\Application;
use Infrastructure\Auth\Exceptions\InvalidCredentialsException;
use Api\User\Repositories\UserRepository;
use Api\Domain\Repositories\DomainRepository;
use Api\User\Repositories\Contact_emailRepository;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use Api\User\Exceptions\UserDisabledException;
use Webpatser\Uuid\Uuid;

class UserPasswordService
{

    private $request;

    private $userRepository;

    private $domainRepository;

    private $contact_emailRepository;

    public function __construct(
      Application $app,
      UserRepository $userRepository,
      DomainRepository $domainRepository,
      Contact_emailRepository $contact_emailRepository
    )
    {
        $this->userRepository = $userRepository;
        $this->domainRepository = $domainRepository;
        $this->contact_emailRepository = $contact_emailRepository;

        $this->request = $app->make('request');
    }

    public function generateResetToken($email, $domain_name)
    {
        $user = $this->getUserCredentials($email, $domain_name);
        $userCredentials = array_merge(
            $this->request->only('user_email'), $user->toArray()
        );
        $status = Password::sendResetLink($userCredentials);

        return [
            'username' => $userCredentials['username'],
            'domain_uuid' => $userCredentials['domain_uuid'],
        ];
    }

    public function resetPassword($email) 
    {
        $user = $this->getUserCredentials($email);
        $userCredentials = array_merge(
            $this->request->only(
                'user_email','password', 'password_confirmation', 'token'
            ),
            $user->toArray()
        );

        Password::reset(
            $userCredentials,
            function ($user, $password) {
                $data['salt'] = Uuid::generate();
                $data['password'] = md5($data['salt'] . $password);
                $user->fill($data);
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
     * @param $email User email
     * @param $domain_name Domain name to which user belongs
     * @return null|\Api\User\Models\User 
     * @throws InvalidCredentialsException
     */
    public function getUserCredentials($email, $domain_name = null)
    {
        $user = $this->getUserByEmail($email);
        
        // TODO:
        //      Needs to be refactored. Does we need it ?
        //      Maybe use better solution with filter...
        if (is_null($user) && !is_null($domain_name)) {
            $user = $this->getUserByEmailAndDomainName($email, $domain_name);
        }

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

    /**
     * Method to get user by email and domain name.
     * 
     * @param $email Email by which user should be gathered.
     * @param $domain_name Domain name to which user is related
     *                     and by which user should be gathered.
     * @return null|\Api\User\Models\User
     * @throws InvalidCredentialsException
     */
    public function getUserByEmailAndDomainName($email, $domain_name)
    {
        $domain = $this->domainRepository
                       ->getWhere('domain_name', $domain_name)
                       ->first();

        if (empty($domain)) {
            throw new InvalidCredentialsException(
                __('Wrong domain name or domain doesn\'t exists')
            );
        }

        // Check for the email in the current domain
        $contact_email = $this->contact_emailRepository
                              ->getWhereArray(['domain_uuid' => $domain->domain_uuid,
                                                 'email_address' => $email])
                              ->first();

        if ($contact_email->count() < 1) {
            throw new InvalidCredentialsException(__('Wrong contact email or email doesn\'t exists'));
        }

        // Only first user ? What about others ?
        $user = $this->userRepository
                        ->getWhereArray(['contact_uuid' => $contact_email->contact_uuid,
                                        'domain_uuid' => $domain->domain_uuid])
                        ->first();
        

        if (!is_null($user)) {

            if ($user->user_enabled != 'true') {
              throw new UserDisabledException();
            }

            return $user;
        }

        throw new InvalidCredentialsException(__('User doesn\'t exists'));
    } 
}