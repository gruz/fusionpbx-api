<?php

namespace App;

use Ratchet\ConnectionInterface;

use Askedio\LaravelRatchet\RatchetServer as RatchetServerBase;

use GrahamCampbell\Throttle\Facades\Throttle;

use Illuminate\Support\Facades\Auth;

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


    // ~ function __construct($console)
    // ~ {
        // ~ parent::__construct($console);
        // ~ // Require if you want to use MakesHttpRequests
        // ~ $this->baseUrl = request()->getSchemeAndHttpHost();
        // ~ $this->app     = app();
    // ~ }

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
      if (isset($conn->user_uuid))
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

        $input = json_decode($input);

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

        if ($input->command != 'login' && !$this->auth($conn)) {
            throw new \App\Exceptions\Socket\NeedToLoginFirst();
            return;
        }


        $data = !empty($input->data) ? $input->data : [];
        $this->context = !empty($input->context) ? $input->context : null;
        switch ($input->command) {
          case 'login':

            if (empty($input->data) || empty($input->data->token))
            {
              throw new \App\Exceptions\Socket\InvalidJSONInput();
              return;
            }

            $token = $input->data->token;

            $server = ['HTTP_AUTHORIZATION' => 'Bearer ' . $token];

            $app = require __DIR__.'/../bootstrap/app.php';
            $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
            $response = $kernel->handle(
                $request = \Illuminate\Http\Request::create('/user', 'GET', [], [], [], $server)
            );

            // ~ var_dump(Auth::id());
            $controllerResult = $response->getContent();
            $kernel->terminate($request, $response);

            // ~ echo $controllerResult;
            $responseData = json_decode($controllerResult);


            if (empty($responseData->user_uuid))
            {
              throw new App\Auth\Exceptions\InvalidCredentialsException();
              return;
            }

            $user = $responseData;

            $conn->user_uuid = $user->user_uuid;
            $conn->domain_uuid = $user->domain_uuid;


            $active_users = !empty($this->domains[$user->domain_uuid]) ? $this->domains[$user->domain_uuid] : [];

// ~ var_dump(($active_users));

            if (isset($active_users[$user->user_uuid]))
            {
                unset($active_users[$user->user_uuid]);
            }

            $response = [
              'domain_uuid' => $user->domain_uuid,
              'active_users' => $active_users,
            ];

            unset($active_users);

            $this->domains[$user->domain_uuid][$user->user_uuid] = $user;
// ~ var_dump($this->domains);
            //$user->conn = $conn;
            $this->users[$user->user_uuid] = $user;
            $this->sendData($conn, $message = null , $action = null, $data = [], $status = 'ok', $context = $this->context);

            // ~ echo json_encode($response, JSON_PRETTY_PRINT);

            break;
          default :
            $this->{$input->command}($conn, $input);
            break;
        }


return;

        $this->send($conn, 'Hello you.');

        $this->sendAll('Hello everyone.');

        $this->send($conn, 'Wait, I don\'t know you! Bye bye!');

        $this->abort($conn);
    }

    protected function setStatus($conn, $input)
    {
      if (!isset($input->data->user_status))
      {
          throw new \App\Exceptions\Socket\InvalidJSONInput();
          return;
      }

      $data = [];
      $data['users'] = [];
      $data['users'][] = [
        'user_uuid' => $conn->user_uuid,
        'user_status' => $input->data->user_status,
      ];

      if (isset($this->domains[$conn->domain_uuid]) && isset($this->domains[$conn->domain_uuid][$conn->user_uuid]))
      {
        $this->domains[$conn->domain_uuid][$conn->user_uuid]->user_status = $input->data->user_status;
      }


      switch ($input->data->user_status) {
        case 'offline':
            $this->abort($conn);
          break;
        case 'invisible':
          $data['users'][$conn->user_uuid]['user_status'] = 'offline';
          break;
        default :

          break;
      }

      $this->sendData($conn, $message = null , $action = null, null, $status = 'ok', $context = $this->context);
      $this->sendAll($message = null , $action = 'update', $data, $status = 'ok', $error_code = null, $error_data = null);

    }

    protected function getUsers($conn, $input)
    {
      $users = $this->domains[$conn->domain_uuid];

      foreach ($users as $user_uuid => $user)
      {
        if ($conn->user_uuid != $user_uuid && $user->user_status == 'invisible')
        {
          // ~ unset($users[$user_uuid]);
          continue;
        }
        else
        {
          $users_to_output[] = [
            'user_uuid' => $user->user_uuid,
            'user_status' => $user->user_status,
          ];
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
      if (!empty($conn->user_uuid))
      {
        $input = new \stdClass;
        $input->command = 'setStatus';
        $input->data = new \stdClass;
        $input->data->user_status = 'offline';

        $this->setStatus($conn, $input);
      }
        $this->clearConnGarbage($conn);
        $this->clients->detach($conn);
        $this->console->error(sprintf('Disconnected: %d', $conn->resourceId));
    }

    protected function clearConnGarbage(ConnectionInterface  $conn)
    {
      if (isset($conn->user_uuid) && isset($this->domains[$conn->domain_uuid]))
      {
        unset($this->domains[$conn->domain_uuid][$conn->user_uuid]);

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
        $message = $exception->getMessage();
        // ~ $conn->close();

        if (isset($conn->user_uuid))
        {
          $user_uuid = $conn->user_uuid;
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
}
