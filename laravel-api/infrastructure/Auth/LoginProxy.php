<?php

namespace Infrastructure\Auth;

use Illuminate\Foundation\Application;
use Infrastructure\Auth\Exceptions\InvalidCredentialsException;
use Api\User\Repositories\UserRepository;
use Api\User\Repositories\DomainRepository;
use Api\User\Repositories\Contact_emailRepository;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use Api\User\Exceptions\UserDisabledException;
use Illuminate\Events\Dispatcher;
use Infrastructure\Events\ResetPasswordLinkWasRequested;
use Webpatser\Uuid\Uuid;



use Exception;

class LoginProxy
{
    const REFRESH_TOKEN = 'refreshToken';

    private $apiConsumer;

    private $auth;

    private $cookie;

    private $db;

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

        $this->apiConsumer = $app->make('apiconsumer');
        $this->auth = $app->make('auth');
        $this->cookie = $app->make('cookie');
        $this->db = $app->make('db');
        $this->request = $app->make('request');
    }

    /**
     * Attempt to create an access token using user credentials
     *
     * @param string $username
     * @param string $password
     */
    public function attemptLogin($username, $password, $domain_name)
    {
        $domain = $this->domainRepository->getWhere('domain_name', $domain_name)->first();

        if (empty($domain))
        {
          throw new InvalidCredentialsException(__('Wrong domain name or domain doesn\'t exists'));
        }

        $user = $this->userRepository->getWhereArray(['username' => $username, 'domain_uuid' => $domain->domain_uuid])->first();

        if (!is_null($user)) {

            if ($user->user_enabled != 'true')
            {
              throw new UserDisabledException();
            }

            return $this->proxy('password', [
                'username' => ['username' => $username, 'domain_uuid' => $domain->domain_uuid],
                'password' => $password,
                'user_uuid' => $user->user_uuid,
                'domain_uuid' => $user->domain_uuid,
            ]);
        }

        throw new InvalidCredentialsException(__('User doesn\'t exists'));
    }

    /**
     * Attempt to refresh the access token used a refresh token that
     * has been saved in a cookie
     */
    public function attemptRefresh()
    {
        $refreshToken = $this->request->cookie(self::REFRESH_TOKEN);

        return $this->proxy('refresh_token', [
            'refresh_token' => $refreshToken
        ]);
    }

    /**
     * Proxy a request to the OAuth server.
     *
     * @param string $grantType what type of grant type should be proxied
     * @param array $data the data to send to the server
     */
    public function proxy($grantType, array $data = [])
    {
        $ret = [];

        if (isset($data['user_uuid']))
        {
          $ret['user_uuid'] = $data['user_uuid'];
        }

        if (isset($data['username']))
        {
          $ret['username'] = $data['username']['username'];
        }

        if (isset($data['domain_uuid']))
        {
          $ret['domain_uuid'] = $data['domain_uuid'];
        }

        $data = array_merge($data, [
            'client_id'     => env('PASSWORD_CLIENT_ID'),
            'client_secret' => env('PASSWORD_CLIENT_SECRET'),
            'grant_type'    => $grantType
        ]);

        $response = $this->apiConsumer->post('/oauth/token', $data);

        if (!$response->isSuccessful()) {
            throw new InvalidCredentialsException();
        }

        $data = json_decode($response->getContent());

        // Create a refresh token cookie
        $this->cookie->queue(
            self::REFRESH_TOKEN,
            $data->refresh_token,
            864000, // 10 days
            null,
            null,
            false,
            true // HttpOnly
        );

        $ret = array_merge($ret, [
            'access_token' => $data->access_token,
            'expires_in' => $data->expires_in,
        ]);

        return $ret;
    }

    /**
     * Logs out the user. We revoke access token and refresh token.
     * Also instruct the client to forget the refresh cookie.
     */
    public function logout()
    {
        $accessToken = $this->auth->user()->token();

        $refreshToken = $this->db
            ->table('oauth_refresh_tokens')
            ->where('access_token_id', $accessToken->id)
            ->update([
                'revoked' => true
            ]);

        $accessToken->revoke();

        $this->cookie->queue($this->cookie->forget(self::REFRESH_TOKEN));
    }

    public function attemptGenerateResetLink($email, $domain_name)
    {
        $domain = $this->domainRepository->getWhere('domain_name', $domain_name)->first();

        if (empty($domain))
        {
          throw new InvalidCredentialsException(__('Wrong domain name or domain doesn\'t exists'));
        }

        // Check for the email in the current domain
        $contact_email = $this->contact_emailRepository
                              ->getWhereArray(['domain_uuid' => $domain->domain_uuid,
                                               'email_address' => $email])
                              ->first();

        if ($contact_email->count() < 1) {
            // throw new EmailNotFoundException();
            throw new Exception("Contact with provided email have not been found.");
        }

        // $contact = $this->contactRepository
        //                 ->getWhere('contact_uuid' => $contactUuid)
        //                 ->first();

        // if ($contact->count() < 1) {
        //     throw new ContactNotFoundException();
        // }

        $user = $this->userRepository
                     ->getWhereArray(['contact_uuid' => $contact_email->contact_uuid,
                                      'domain_uuid' => $domain->domain_uuid])
                     ->first();

        if (!is_null($user)) {

            if ($user->user_enabled != 'true') {
              throw new UserDisabledException();
            }

            $this->request->validate(['user_email' => 'required|email']);

            // $resetPasswordToken = Password::sendResetLink(
            //     $user->toArray(),
            //     function ($user, $token) use (&$tempToken){
            //         $this->dispatcher->dispatch(new ResetPasswordLinkWasRequested($user, $token));
            //     }
            // );

            $userBasedOnCredentials = Password::getUser($user->toArray());

            if (is_null($userBasedOnCredentials)) {
                throw new Exception(Password::INVALID_USER);
            }

            if (Password::getRepository()->recentlyCreatedToken($userBasedOnCredentials)) {
                throw new Exception(Password::RESET_THROTTLED);
            }

            $token = Password::createToken($userBasedOnCredentials);

            $this->dispatcher->dispatch(new ResetPasswordLinkWasRequested($user, $token));

            return [
                'username' => $user->username,
                'domain_uuid' => $user->domain_uuid,
                'token' => $token
            ];
        }

        throw new InvalidCredentialsException(__('User doesn\'t exists'));
    }

    public function attemptResetPassword($email) 
    {
        // Check for the email in the current domain
        $contact_email = $this->contact_emailRepository
                              ->getWhere('email_address', $email)
                              ->first();

        if ($contact_email->count() < 1) {
            // throw new EmailNotFoundException();
            throw new Exception("Contact with provided email have not been found.");
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
