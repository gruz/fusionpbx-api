<?php

namespace Api\User\Services;

use Illuminate\Foundation\Application;
use Infrastructure\Auth\Exceptions\InvalidCredentialsException;
use Api\User\Repositories\UserRepository;
use Api\Domain\Repositories\DomainRepository;
use Api\User\Repositories\Contact_emailRepository;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use Api\User\Exceptions\UserDisabledException;
use Illuminate\Events\Dispatcher;
use Api\User\Events\ResetPasswordLinkWasRequested;
use Webpatser\Uuid\Uuid;
use Infrastructure\Auth\Exceptions\ContactEmailNotFoundException;
use Infrastructure\Auth\Exceptions\DomainNotFoundException;

class UserPasswordService
{

    private $request;

    private $userRepository;

    private $domainRepository;

    private $contact_emailRepository;

    private $dispatcher;

    public function __construct(
      Application $app,
      UserRepository $userRepository,
      DomainRepository $domainRepository,
      Contact_emailRepository $contact_emailRepository,
      Dispatcher $dispatcher
    )
    {
        $this->userRepository = $userRepository;
        $this->domainRepository = $domainRepository;
        $this->contact_emailRepository = $contact_emailRepository;
        $this->dispatcher = $dispatcher;

        $this->request = $app->make('request');
    }

    public function generateResetToken($email, $domain_name)
    {

        $domain = $this->domainRepository->getWhere('domain_name', $domain_name)->first();

        if (empty($domain)) {
          throw new InvalidCredentialsException(__('Wrong domain name or domain doesn\'t exists'));
        }

        // Check for the email in the current domain
        $contact_email = $this->contact_emailRepository
                              ->getWhereArray(['domain_uuid' => $domain->domain_uuid,
                                               'email_address' => $email])
                              ->first();

        if ($contact_email->count() < 1) {
            throw new InvalidCredentialsException(__('Wrong contact email or email doesn\'t exists'));
        }

        // $contact = $this->contactRepository
        //                 ->getWhere('contact_uuid' => $contactUuid)
        //                 ->first();

        // if ($contact->count() < 1) {
        //     throw new ContactNotFoundException();
        // }

        // Only first user ? What about others ?
        $user = $this->userRepository
                     ->getWhereArray(['contact_uuid' => $contact_email->contact_uuid,
                                      'domain_uuid' => $domain->domain_uuid])
                     ->first();

        if (!is_null($user)) {

            if ($user->user_enabled != 'true') {
              throw new UserDisabledException();
            }

            // $resetPasswordToken = Password::sendResetLink(
            //     $user->toArray(),
            //     function ($user, $token) use (&$tempToken){
            //         $this->dispatcher->dispatch(new ResetPasswordLinkWasRequested($user, $token));
            //     }
            // );

            $userBasedOnCredentials = Password::getUser($user->toArray());

            if (is_null($userBasedOnCredentials)) {
                throw new InvalidCredentialsException(__('Wrong user credentials:' . Password::INVALID_USER));
            }

            if (Password::getRepository()->recentlyCreatedToken($userBasedOnCredentials)) {
                throw new InvalidCredentialsException(__('Wrong user credentials:' . Password::RESET_THROTTLED));
            }

            $token = Password::createToken($userBasedOnCredentials);

            // send user notification about password reset action with link to reset
            $this->dispatcher->dispatch(new ResetPasswordLinkWasRequested($user, $token));

            return [
                'username' => $user->username,
                'domain_uuid' => $user->domain_uuid,
                'token' => $token
            ];
        }

        throw new InvalidCredentialsException(__('User doesn\'t exists'));
    }

    public function resetPassword($email) 
    {
        // Check for the email in the current domain
        $contact_email = $this->contact_emailRepository
                              ->getWhere('email_address', $email)
                              ->first();

        if ($contact_email->count() < 1) {
            // throw new EmailNotFoundException();
            throw new InvalidCredentialsException("Contact with provided email have not been found.");
        }

        $user = $this->userRepository
                     ->getWhere('contact_uuid', $contact_email->contact_uuid)
                     ->first();

        if (!is_null($user)) {

            if ($user->user_enabled != 'true') {
              throw new UserDisabledException();
            }
                
            $userCredentials = array_merge(
                $this->request->only('emails','password', 'password_confirmation', 'token'),
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

        }
        
        return [
            'success' => 'Password has been successfully reset',
        ];
    }
}