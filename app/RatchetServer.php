<?php

namespace App;

use Ratchet\ConnectionInterface;

use Askedio\LaravelRatchet\RatchetServer as RatchetServerBase;

use GrahamCampbell\Throttle\Facades\Throttle;

use Illuminate\Support\Facades\Auth;

use Carbon\Carbon;

use Api\Status\Services\StatusService;
use Api\User\Services\UserService;


class RatchetServer extends RatchetServerBase
{
    use Traits\SocketJSONHelper;

    /**
     * Clients.
     *
     * @var [type]
     */
    protected $domains = [];

    protected $context = null;

    private $statusService;
    private $userService;
    private $auth;

    public function __construct($console)
    {
        parent::__construct($console);

        $this->statusService = app(StatusService::class);
        $this->userService = app(userService::class);
        $this->auth = app();
    }

    public function onOpen(ConnectionInterface $conn) {
        // ~ parent::onOpen($conn);

        $this->conn = $conn;

        $this->attach()->throttle()->limit();

        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";
    }

    protected function auth($conn)
    {
      if (isset($conn->user->user_uuid))
      {
        return true;
      }
      else
      {
        return false;
      }
    }

    public function onMessage(ConnectionInterface $conn, $input)
    {
        $this->conn = $conn;

        if (empty(trim($input)))
        {
          return;
        }

        $startHash = '!SmertMosklaliam#678#Start';
        $endHash = '!SmertMosklaliam#678#End';

        $hasStart = preg_match('~'.$startHash. '(.*)' .  '~s', $input, $startMatches);

        $hasEnd = preg_match('~' . '(.*)' . $endHash .'~s', $input, $endMatches);
        // Is there is both start end hashes - the message is passed at once
        if ($hasStart && $hasEnd)
        {
          preg_match('~'.$startHash. '(.*)' . $endHash .  '~s', $input, $matches);
          if (!empty($matches[1]))
          {
            $input = $matches[1];
          }
        }
        // If there is only start hash, then store the message part to the $conn object
        elseif ($hasStart)
        {
          $this->conn->message = $startMatches[1];
          return;
        }
        // If there is endHash, but there was no any start message, then it's an error
        elseif ($hasEnd && !isset($this->conn->message))
        {
          throw new \App\Exceptions\Socket\InvalidJSONInput();
          return;
        }
        // If is no start and end hash and there is no conn->message, then there was no start - error happaned
        elseif (!$hasStart && !$hasEnd && !isset($this->conn->message))
        {
          throw new \App\Exceptions\Socket\InvalidJSONInput();
          return;
        }
        // If is no start and end hash and there is conn->message, then it's a next message part - append it
        elseif (!$hasStart && !$hasEnd && isset($this->conn->message))
        {
          $this->conn->message .= $input;
          return;
        }
        // If there is end hash and there is the started message in conn->message, the prepare the message and use it
        elseif ($hasEnd && isset($this->conn->message))
        {
          $input = $this->conn->message . $endMatches[1];
        }

        $this->context = null;
        $this->console->comment(sprintf('Message from %d: %s', $conn->resourceId, $input));

        if ($this->isThrottled($conn, 'onMessage')) {
            $this->console->info(sprintf('Message throttled: %d', $conn->resourceId));
            // ~ $this->send($conn, trans('ratchet::messages.toManyMessages'));

            if ($input = json_decode($input) && !empty($input->context))
            {
              $this->context = $input->context;
            }

            throw new \App\Exceptions\Socket\TooManyMessages();
            $this->throttled = true;

            if (config('ratchet.abortOnMessageThrottle')) {
                $this->abort($conn);
            }
        }

        if (empty(trim($input)))
        {
          return;
        }

        $json_decoded = json_decode($input);

        if ($json_decoded === null)
        {
          $input = base64_decode($input);
          $this->console->comment(sprintf('Message from %d: %s', $conn->resourceId, $input));
          $input = json_decode($input);
        }
        else
        {
          $input = $json_decoded;
        }


        if (!$input)
        {
          throw new \App\Exceptions\Socket\InvalidJSONInput();
          return;
        }

        if (empty($input->command))
        {
          throw new \App\Exceptions\Socket\NoCommadException();
          return;
        }

        if (!$this->auth($conn) && !in_array($input->command,  ['post.login', 'post.login2'])) {
            throw new \App\Exceptions\Socket\NeedToLoginFirst();
            return;
        }

        $data = !empty($input->data) ? $input->data : [];
        $this->context = !empty($input->context) ? $input->context : null;

        $HTTP_method = explode ('.', $input->command, 2);
        $HTTP_url = $HTTP_method[1];
        $HTTP_method = strtoupper($HTTP_method[0]);

        if (in_array($HTTP_url, ['put', 'post', 'get', 'delete']))
        {
          throw new \App\Exceptions\Socket\InvalidJSONInput();
          return;
        }

        if (in_array($HTTP_url, ['put', 'post']) && empty($input->data))
        {
          throw new \App\Exceptions\Socket\InvalidJSONInput();
          return;
        }

        if (!empty($input->data))
        {
          $data = (array) $input->data;
        }
        else
        {
          $data = [];
        }

        $command_stripped = explode('/', $input->command, 2);

        $sendAll = false;

        switch ($command_stripped[0]) {
          case 'post.login2':

            if (empty($data) || empty($data['token']) || empty($data['refreshToken']) || empty($data['expires_in']))
            {
              throw new \App\Exceptions\Socket\InvalidJSONInput();
              return;
            }

            $token = $data['token'];
            $server = ['HTTP_AUTHORIZATION' => 'Bearer ' . $token];

            $response = $this->runController('/user', 'GET', [], [], [], $server);

            $responseData = $response->getData();

            if (empty($responseData->user_uuid))
            {
              throw new \App\Auth\Exceptions\InvalidCredentialsException();
              return;
            }

            $user = $responseData;
            $user->access_token = $token;

            $user->expires = Carbon::now()->addSeconds($data['expires_in']);
            $user->refreshToken = $data['refreshToken'];

            $this->initStatus($user);

            $conn->user = $user;

            $this->domains[$user->domain_uuid][$user->user_uuid] = &$conn->user;

            $this->users[$user->user_uuid] = &$conn->user;

            $this->sendData($conn, $message = null , $action = null, $data = [], $status = 'ok', $context = $this->context);

            return;

            break;

          case 'post.login':

            $response = $this->runController($HTTP_url, $HTTP_method, $data, [], [], []);
            $controllerResult = $response->getData();

            if (!empty($controllerResult->status) && $controllerResult->status == 'error')
            {
              throw new \App\Auth\Exceptions\InvalidCredentialsException();
              return;
            }

            $user = new \stdClass;

            foreach ($controllerResult as $k => $v)
            {
              $user->{$k} = $v;

            }

            // ~ $user->user_uuid = $controllerResult->user_uuid;
            // ~ $user->domain_uuid = $controllerResult->domain_uuid;
            // ~ $user->access_token = $controllerResult->access_token;

            $user->refreshToken = $response->headers->getCookies()[0]->getValue();
            $user->expires = Carbon::now()->addSeconds($controllerResult->expires_in);

            $this->initStatus($user);

            $conn->user = $user;

            $this->domains[$user->domain_uuid][$user->user_uuid] = &$conn->user;

            $this->users[$user->user_uuid] = &$conn->user;

            $this->sendData($conn, $message = null , $action = null, $data = [], $status = 'ok', $context = $this->context);

            return;

            break;

          case 'get.user':
          case 'get.users':
            //$responseKey = 'users';
            $data['includes']  = ['status'];
            break;
          case 'post.status':
            // ~ $responseKey = 'data';
            $sendAction = 'update';
            $sendAll = true;

            break;

          default :
            $responseKey = 'data';
            $this->{$input->command}($conn, $input);
            break;
        }

        switch ($HTTP_method) {
          case 'GET':
            $action = 'data';
            break;
          case 'POST':
            $action = 'insert';
            break;
          case 'DELETE':
            $action = 'delete';
            break;
          case 'PUT':
          default :
            $action = 'update';

            break;
        }

        if (!isset($sendAction))
        {
          $sendAction = $action;
        }

        // Set http header Authorization: Bearer ACCESS_TOKEN
        $server = $this->setBearer();

        $response = $this->runController($HTTP_url, $HTTP_method, $data, [], [], $server);

        $responseData = json_decode($response->getContent(), true);


        switch ($command_stripped[0]) {
          case 'post.status':
            // Update current connection with the user state
            $conn->user->user_status = $responseData['users']['user_status'];

            if ($responseData['users']['user_status'] == 'invisible')
            {
              $responseData['users']['user_status'] = 'offline';
            }

            $responseData['users'] = [$responseData['users']];

            break;
          case 'get.users':
            // Remove users without statuses
            if (!empty($responseData['users']))
            {

              foreach ($responseData['users'] as $k => $user)
              {
                if (!isset($user['status']))
                {
                  $responseData['users'][$k]['user_status'] = 'offline';
                  $responseData['users'][$k]['status'] = ['user_status' => 'offline'];
                }
                elseif (in_array($user['status']['user_status'], ['invisible', 'offline']))
                {
                  $responseData['users'][$k]['user_status'] = 'offline';
                }
                else
                {
                  $responseData['users'][$k]['user_status'] = $user['status']['user_status'];
                }
              }

              $responseData['users'] = array_values($responseData['users']);

            }

            break;
          default :

            break;
        }

        if (!empty($responseData))
        {
          if (!empty($responseKey))
          {
            $controllerResult = [$responseKey => $responseData];
          }
          else
          {
            $controllerResult = $responseData;
          }

          // Send response to the user
          $this->sendData($conn, $message = null , $sendAction, $data = $controllerResult, $status = 'ok', $context = $this->context);
        }
        else
        {
          $this->sendData($conn, $message = null , $sendAction = null, $data = [], $status = 'ok', $context = $this->context);
        }

        // Send broadcast messages if needed

        if ($sendAll)
        {
          $this->sendAll($message = null , $sendAction, $responseData, $status = 'ok', $error_code = null, $error_data = null);
        }

return;

        $this->send($conn, 'Hello you.');

        $this->sendAll('Hello everyone.');

        $this->send($conn, 'Wait, I don\'t know you! Bye bye!');

        $this->abort($conn);
    }

    protected function initStatus(&$user)
    {
      $user_status = $this->statusService->findUserStatus($user->user_uuid);
      $userObject = $this->userService->getById($user->user_uuid);

      // Pass to the service auth object to reauth the user
      $auth = app(\Illuminate\Auth\AuthManager::class);
      // ~ $auth->onceUsingId($user->user_uuid);

      //Auth::setUser($userObject, $remember = false);
      Auth::guard('api')->setUser($userObject, $remember = false);

      // ~ $this->statusService->auth = app(\Illuminate\Auth\AuthManager::class);

      if (empty($user_status))
      {
        $statusData = [
          'user_status' => 'offline',
          'domain_uuid' => $user->domain_uuid,
          'user_uuid' => $user->user_uuid,
          'status_lifetime' => config('api.status_lifetime'),
        ];

        $user_status = $this->statusService->create($statusData);
      }
      else
      {
        // Time when the status is treated as dead
        $deadTime = Carbon::now()->subSeconds(config('api.status_lifetime'));

        if ($user_status->updated_at <= $deadTime)
        {
          $statusData = [
            'user_status' => 'offline',
          ];

          $user_status = $this->statusService->update($user->user_uuid, $statusData);
        }
      }

      $user->user_status = $user_status->user_status;
    }

    protected function getAccessToken()
    {
      $conn = $this->conn;

      if ($conn->user->expires >= Carbon::now())
      {
        $access_token = $conn->user->access_token;
      }
      else
      {
        $cookies = ['refreshToken' => $conn->user->refreshToken];

        $response = $this->runController('login/refresh', 'POST', [], $cookies, [], []);

        $controllerResult = $response->getData();

        $conn->user->refreshToken = $response->headers->getCookies()[0]->getValue();

        $access_token = $controllerResult->access_token;

        $conn->user->access_token = $controllerResult->access_token;
        $conn->user->expires = Carbon::now()->addSeconds($controllerResult->expires_in);

      }

      return $access_token;
    }

    protected function setBearer()
    {

      $access_token = $this->getAccessToken();

      $server = ['HTTP_AUTHORIZATION' => 'Bearer ' . $access_token];

      return $server;
    }

    protected function runController($HTTP_url, $HTTP_method, $parameters = [], $cookies = [],  $files = [], $server = [])
    {
        if (isset($this->conn->user) && !empty($this->conn->user->user_uuid))
        {
          $userObject = $this->userService->getById($this->conn->user->user_uuid);
          Auth::guard('api')->setUser($userObject, $remember = false);
        }

        $app = require __DIR__.'/../bootstrap/app.php';
        $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

        $response = $kernel->handle(
            $request = \Illuminate\Http\Request::create($HTTP_url, $HTTP_method, $parameters, $cookies,  $files, $server)
        );

        $kernel->terminate($request, $response);

        return $response;
    }

    protected function setStatus($conn, $input)
    {
      // If there is not status info in the JSON input, then throw extension
      if (!isset($input['user_status']))
      {
          throw new \App\Exceptions\Socket\InvalidJSONInput();
          return;
      }

      // Shortcut
      $domain_uuid = $conn->user->domain_uuid;
      $user_uuid = $conn->user->user_uuid;

      $old_status = empty($conn->user->user_status) ? 'offline' : $conn->user->user_status;

      // Set new user status in the internal storage
      if (isset($this->domains[$domain_uuid]) && isset($this->domains[$domain_uuid][$user_uuid]))
      {
        $conn->user->user_status = $input['user_status'];

        if ($input['user_status'] == 'offline')
        {
          unset($this->domains[$domain_uuid][$user_uuid]);
        }
      }

      // Prepare an array to be returned containing one user
      $data = [];
      $data['users'] = [];

      switch ($input['user_status']) {
        case 'offline':
        case 'invisible':
          $data['users'][] = [
            'user_uuid' => $user_uuid,
            'user_status' => 'offline',
          ];

          break;
        default :
          $data['users'][] = [
            'user_uuid' => $user_uuid,
            'user_status' => $input['user_status'],
          ];
          break;
      }

      $this->sendData($conn, $message = null , $action = null, null, $status = 'ok', $context = $this->context);

      if ($old_status == $conn->user->user_status ||
        (in_array($conn->user->user_status, ['offline', 'invisible']) && in_array($old_status, ['offline', 'invisible']))
      )
      {
        // In this case we don't need to inform all
      }
      else
      {
        $this->sendAll($message = null , $action = 'update', $data, $status = 'ok', $error_code = null, $error_data = null);
      }
    }

    protected function getUsers($conn, $input)
    {
      $users = $this->domains[$conn->user->domain_uuid];

      $users_to_output = [];

      foreach ($users as $user_uuid => $user)
      {
        if ($conn->user->user_uuid != $user->user_uuid && $user->user_status == 'invisible')
        {
          // ~ unset($users[$user_uuid]);
          continue;
        }
        else
        {
          $tmp_arr = [];
          foreach ($user as $k => $v)
          {
            if (in_array($k, ['access_token', 'refreshToken', 'expires', 'expires_in']))
            {
              continue;
            }

            $tmp_arr[$k] = $v;
          }

          $users_to_output[] = $tmp_arr;
          // ~ $users_to_output[] = [
            // ~ 'user_uuid' => $user->user_uuid,
            // ~ 'user_status' => $user->user_status,
          // ~ ];
        }
      }

      $data = [];
      $data['users'] = [];
      $data['users'] = $users_to_output;

      $this->sendData($conn, $message = null , $action = 'update', $data, $status = 'ok', $context = null);
    }

    /**
     * Close the current connection.
     *
     * @return [type] [description]
     */
    public function abort(ConnectionInterface $conn)
    {
      $this->clearConnGarbage($conn);
      $this->clients->detach($conn);
      $conn->close();
    }

    /**
     * Perform action on close.
     *
     * @param ConnectionInterface $conn [description]
     *
     * @return [type] [description]
     */
    public function onClose(ConnectionInterface $conn)
    {
      $this->conn = $conn;
      if (!empty($conn->user->user_uuid))
      {
        $input = ['user_status' => 'offline'];

        //$this->setStatus($conn, $input);
      }

      $this->clearConnGarbage($conn);
      $this->clients->detach($conn);
      $this->console->error(sprintf('Disconnected: %d', $conn->resourceId));
    }

    protected function clearConnGarbage(ConnectionInterface  $conn)
    {
      if (isset($conn->user->user_uuid) && isset($this->domains[$conn->user->domain_uuid]))
      {
        unset($this->domains[$conn->user->domain_uuid][$conn->user->user_uuid]);

      }
    }

    /**
     * Perform action on error.
     *
     * @param ConnectionInterface $conn      [description]
     * @param Exception           $exception [description]
     *
     * @return [type] [description]
     */
    public function onError(ConnectionInterface $conn, \Exception $exception)
    {
        $this->conn = $conn;
        $message = $exception->getMessage();
        // ~ $conn->close();

        if (isset($conn->user->user_uuid))
        {
          $user_uuid = $conn->user->user_uuid;
        }
        else
        {
          $user_uuid = 'guest';
        }

        $debug = config('app.debug');

        if ($debug)
        {
          $this->console->error($exception->__toString());
          $this->sendError($conn, $exception->getCode(), $message, $exception->__toString());
        }
        else
        {
          $this->console->error(sprintf('Error: %s . Code: %s . User UUID: %s', $message, $exception->getCode(), $user_uuid));
          $this->sendError($conn, $exception->getCode(), $message);
        }
    }

    private function attach()
    {
        $this->clients->attach($this->conn);
        $this->console->info(sprintf('Connected: %d', $this->conn->resourceId));
        $this->connections = count($this->clients);
        $this->console->info(sprintf('%d %s', $this->connections, str_plural('connection', $this->connections)));

        return $this;
    }

    /**
     * Throttle connections.
     *
     * @return [type] [description]
     */
    private function throttle()
    {
        if ($this->isThrottled($this->conn, 'onOpen')) {
            $this->console->info(sprintf('Connection throttled: %d', $this->conn->resourceId));
            // ~ $this->conn->send(trans('ratchet::messages.toManyConnectionAttempts'));
            $this->onError($this->conn, new \App\Exceptions\Socket\TooManyConnectionAttempts());
            $this->throttled = true;
            $this->conn->close();
        }

        return $this;
    }

    /**
     * Limit connections.
     *
     * @return [type] [description]
     */
    private function limit()
    {
        if ($connectionLimit = config('ratchet.connectionLimit') && $this->connections - 1 >= $connectionLimit) {
            $this->console->info(sprintf('To many connections: %d of %d', $this->connections - 1, $connectionLimit));
            // ~ $this->conn->send(trans('ratchet::messages.toManyConnections'));
            $this->onError($this->conn, new \App\Exceptions\Socket\TooManyConnections());
            $this->conn->close();
        }

        return $this;
    }

    /**
     * Check if the called function is throttled.
     *
     * @param [type] $conn    [description]
     * @param [type] $setting [description]
     *
     * @return bool [description]
     */
    private function isThrottled($conn, $setting)
    {
        $connectionThrottle = explode(':', config(sprintf('ratchet.throttle.%s', 'onMessage')));
        $connectionThrottle = explode(':', config(sprintf('ratchet.throttle.%s', $setting)));

        return !Throttle::attempt([
          'ip'    => $conn->remoteAddress,
          'route' => $setting,
        ], $connectionThrottle[0], $connectionThrottle[1]);
    }


}


{
  // Some code tries, store for reference purposes
            // ~ $tokenRepository = app(TokenRepository::class);
// ~ var_dump(get_class($tokenRepository));
// ~ var_dump($token);
// ~ $tr = $tokenRepository->find($token);


/* ===================================== Так дає різних юзерів
$data = [
        "domain_name" => "test01",
        "password" => "12341234",
        "username" => "gruzua"
];
// ~ var_dump($tr);
            $server = ['HTTP_AUTHORIZATION' => 'Bearer ' . $token];

            $this->baseUrl = request()->getSchemeAndHttpHost();
            $this->app  = app();

            //$server = [];

            $response = $this->call('POST','/login', $data, [], [], $server);
            $responseData = $response->getData();
var_dump($responseData);

$data = [
        "domain_name" => "test01",
        "password" => "12341234",
        "username" => "gruzua1"
];
// ~ var_dump($tr);
            $server = ['HTTP_AUTHORIZATION' => 'Bearer ' . $token];

            $this->baseUrl = request()->getSchemeAndHttpHost();
            $this->app  = app();

            //$server = [];

            $response = $this->call('POST','/login', $data, [], [], $server);
            $responseData = $response->getData();
var_dump($responseData);
return;

*/

// ~ $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjhlY2M5MGM3NTgzNzA5OWFlMDI3YWVmMjFkY2Q3Yzg1YWQ2ZTc4ODUzOTQzYzFmMzM3ZjYxNDdmNmFhOWM2NmE4NWI2Y2YxOTQwOGUwYjY5In0.eyJhdWQiOiIyIiwianRpIjoiOGVjYzkwYzc1ODM3MDk5YWUwMjdhZWYyMWRjZDdjODVhZDZlNzg4NTM5NDNjMWYzMzdmNjE0N2Y2YWE5YzY2YTg1YjZjZjE5NDA4ZTBiNjkiLCJpYXQiOjE0OTk2NjM0MjMsIm5iZiI6MTQ5OTY2MzQyMywiZXhwIjoxNDk5NjY0MDIzLCJzdWIiOiJjMTYxNTQ4MC00ZDdiLTExZTctYWExYy1jNzY4MGM0YWFjNmYiLCJzY29wZXMiOltdfQ.Zg6t5h6thC-xQOpkjwtOH8aaH84rCk6XxeGQHyB9D0wmvjaHipFwPpEZTPgyLRAt_l29KAI-8D38o7DqN7szTHPTJAEDYcjtpT5eM1cNudG5BYhsY-AdsLnVp6TdGi02Bho82DIFGFzPfqwvv5Xrhpo9VU7VA83cPfdU0RHJS2ywAIMOS5pra5JCxjL8xcR9LOsuPIFSPKD-KY-YTTa_cMN_NF8ubtV4mawXxwXguSmqrNe3IyppodvQbPef2IUGwDAVbXCuXSfqzwIBIwHABF5CttnJVD3CSLaS3aSVv6cqAWkhs558n76gQGCWVgiee1XvblR42EuQsnFp07O0YI2k9fDAcyxNZAQJRF9Z1skNX230u37GZYpPc8Y1TqOX_8y5jH07H4_yWDRivBGjrx40eh7h3y4kdNDDeUc9nAQP0f-cmBPYeviAO2s7Nc6q6_pO9fEerRs7grc5Rase8UkZvutFfdZc3qrcSfs52wel6n4mXFCBZ5tbv5jduFH3hKv15su3o_5f_-YXJKZMuYbBNkzSMKUJne_fTlWJob8-SaY2JBUOw2sEVgpV7Ne12Iyb5dG30kwAIfY2kxvUy3_syLoc_QcJSZWcVIgbnbDH-OBKeaiNWpBql2KofKWSxAkQghruL1eMbtA-AvgNgW5k4X1pO73F46M8TtUgDcs';

            // ~ $server = ['HTTP_AUTHORIZATION' => 'Bearer ' . $token];
            // ~ $response = $this->call('GET','/user?'.uniqid(), $data, [], [], $server);
            // ~ $responseData = $response->getData();
// ~ var_dump($token, $responseData->user_uuid);

// ~ $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImY1ZGM3NDFjYmVjMzhhMWU4NmY5NmY4OTZiYzUwNjI5NjVmZTRhOTEwNDRjMWIzZjU4YzhjNDVlYTgyZjUwNTU3ZmE3NDczMjU3MTE3ZTk1In0.eyJhdWQiOiIyIiwianRpIjoiZjVkYzc0MWNiZWMzOGExZTg2Zjk2Zjg5NmJjNTA2Mjk2NWZlNGE5MTA0NGMxYjNmNThjOGM0NWVhODJmNTA1NTdmYTc0NzMyNTcxMTdlOTUiLCJpYXQiOjE0OTk2NjM0MTgsIm5iZiI6MTQ5OTY2MzQxOCwiZXhwIjoxNDk5NjY0MDE4LCJzdWIiOiIyYTNhZTE3MC00NmIxLTExZTctODUyYy1kNTY5ZTU2NjJkNGEiLCJzY29wZXMiOltdfQ.JBKS6w5bBVDbyiCwSw3IVvev2i12ugbf1tBk36LbgayPVYIkhY5TqWdOpP_mJzXlX5Kmo7I85_lV_3r6KmIs32IwHg_sxyHLi82azUpEEYqnZ8spuyqt5w_BGM0wsC-OQnjk7aMiAIkxHhquoMWJ3vi870pjdJL-zjTyXOmuOMSitktCdBNUHLDC-ZFXWWYtNs8IkGFyPcl1_bGz6iG8nzPABrjKE4PhZKoMvg-eZ0zn5b0-5-WA_xP2z8q1WY_HtC9FXDlYGOlzXvar-7i70LgQ1ME26XPLmSQ1D5Sq99Y4f05bL3mRKbOBjunUGnizGJOzgdXJygCfF5XRCgPDt7W03z2zxn-1j7xADtMXwx5zoY4lxJa8UfrgFOshpQgNmrip3DOZ3Ids7_ISTlbuxxsjLt--UN7fNQZOOvTuF4qnb8Nsum5MD9SH-_Men8V1ypHmyVivOSFuw8A5PeyEvCUYDs-dim9tMFFXWhj22VzcN_egZCtEtIr1u_kYGB1sOumQDJhjql-_xahKvicE1Q3dRbo2u7WQVtjQk2s5ky4_9QsnOHZu48DO8vDnvzJSW_8GjSYEO4hJZ-td5iwHTQH-OM3FgcNDBZLBAFQbdzU4QzTYn8KEEN5EYGT07pciJQCmmL6PrZ4r8fYYyLsuuskJCpmbwqVpx0miTb0ZWF8';
            // ~ $server = ['HTTP_AUTHORIZATION' => 'Bearer ' . $token];
            // ~ $response = $this->call('GET','/user', $data, [], [], $server);
            // ~ $responseData = $response->getData();

return;
            $response = $this->call('GET','/user', $data, [], [], $server);
            $responseData = $response->getData();

            $user = Auth::user();
var_dump($user);
            //Auth::logout();
var_dump($token, $responseData->user_uuid);
\Session::flush();
return;


// ~ var_dump($this->conn->user->user_uuid);
// ~ var_dump(get_class_methods($kernel->getApplication()));
// ~ var_dump(get_class_methods($kernel->getApplication()->make(\Illuminate\Auth\AuthManager::class)));
          // ~ $kernel->guard('api')->setUser($userObject, $remember = false);
          // ~ $auth = $app->make(\Illuminate\Auth\AuthManager::class);
          // ~ $auth->onceUsingId($this->conn->user->user_uuid);
          // ~ $userObject = $this->userService->getById($this->conn->user->user_uuid);
// ~ var_dump($conn->user->user_uuid, $userObject);
          // ~ Auth::setUser($userObject, $remember = false);


}
