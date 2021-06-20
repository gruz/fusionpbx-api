<?php

namespace App\Auth;

use Illuminate\Foundation\Application;
use App\Auth\Exceptions\InvalidCredentialsException;
use Api\User\Repositories\UserRepository;
use Api\Domain\Repositories\DomainRepository;
use App\Exceptions\UserDisabledException;
use Illuminate\Events\Dispatcher;

class LoginProxy
{
    const REFRESH_TOKEN = 'refreshToken';

    /**
     * 
     * @var \Optimus\ApiConsumer\Router
     */
    private $apiConsumer;

    private $auth;

    private $cookie;

    private $db;

    private $request;

    private $userRepository;

    private $domainRepository;

    private $dispatcher;

    public function __construct(
      Application $app,
      UserRepository $userRepository,
      DomainRepository $domainRepository,
      Dispatcher $dispatcher
    )
    {
        $this->userRepository = $userRepository;
        $this->domainRepository = $domainRepository;
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
        // $domain = $this->domainRepository->getWhere('domain_name', $domain_name)->first(); // if domian is not enabled do not allow to login // catch on level of Request

        // if (empty($domain)) {
        //     throw new InvalidCredentialsException(__('Wrong domain name or domain doesn\'t exists'));
        // }

        // $user = $this->userRepository->getWhereArray(['username' => $username, 'domain_uuid' => $domain->domain_uuid])->first();

        $user = $this->userRepository->getUserByUsernameAndDomain($username, $domain_name);

        if (!is_null($user)) {

            if ($user->user_enabled != 'true') {
                throw new UserDisabledException();
            }

            $data = [
                'username' => ['username' => $username, 'domain_uuid' => $user->domain->getAttribute('domain_uuid')],
                'password' => $password,
                'user_uuid' => $user->user_uuid,
                'domain_uuid' => $user->domain_uuid,
            ];

            return $this->proxy('password', $data);
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

        if (isset($data['user_uuid'])) {
            $ret['user_uuid'] = $data['user_uuid'];
        }

        if (isset($data['username'])) {
            $ret['username'] = $data['username']['username'];
        }

        if (isset($data['domain_uuid'])) {
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

}
