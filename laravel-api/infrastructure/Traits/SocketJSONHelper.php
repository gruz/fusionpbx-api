<?php
namespace App\Traits;

use Ratchet\ConnectionInterface;

use Carbon\Carbon;

trait SocketJSONHelper
{

    /**
     * Sends JSON data to the passed connection
     *
     * @param ConnectionInterface $conn
     * @param string              $status      Response status: ok, error
     * @param string              $action      Action performed: e.g. update, insert, delete, get
     * @param array               $data        Corresponding to the action data.
     *                                         E.g. if you get a user info you use $action = 'get', $data = $array_with_user_info
     *                                         $data must be paired with actions
     * @param string              $message     Text message
     * @param integer             $error_code
     * @param mixed               $error_data  If debug is enables, it's recommended to place here exception trace info
     * @param string              $context     If the response is an answer to a client request, the client sends the $context, which we must return
     *                                         to let the client know the request-response pair
     *
     * @return   void
     */
    private function sendJSONResponse(
      ConnectionInterface   $conn,
      string                $status,
      string                $action = null,
                            $data = [],
      string                $message = null,
      int                   $error_code = null,
                            $error_data = null,
      string                $context = null
    )
    {
      $array = [
          'server' => [ 'status' => $status ]
      ];

      if ($context !== null)
      {
        $array['context'] = $context;
      }

      if (!empty($action))
      {
        $array[$action] = $data;
      }

      if (!empty($message))
      {
        $array['server']['message'] = $message;
      }

      if ($error_code !== null)
      {
        $array['server']['error_code'] = $error_code;
      }

      if ($error_data !== null)
      {
        $array['server']['error_data'] = $error_data;
      }

      $response = json_encode($array, JSON_PRETTY_PRINT) . PHP_EOL;

      $this->console->info(Carbon::now() . PHP_EOL . 'Connection ' . $conn->resourceId . " => ". $response);

      $response = chr(2) . $response . chr(3);

      $this->send($conn, $response);
    }

    private function sendData($conn, $message = null , $action = null, $data = [], $status = 'ok', $context = null)
    {
      if ($context === null)
      {
        $context = $this->context;
      }

      $this->sendJSONResponse(
        $conn,
        $status,
        $action,
        $data,
        $message,
        $error_code = null,
        $error_data = null,
        $context
      );
    }

    private function sendError($conn, $error_code = 2000, $message = null, $error_data = null, $context = null)
    {
      if ($context === null)
      {
        $context = $this->context;
      }

      $this->sendJSONResponse(
        $conn,
        $status = 'error',
        $action = null,
        $data = null,
        $message,
        $error_code,
        $error_data,
        $context
      );
    }

    /**
     * Send a message to all connections.
     *
     * @param [type] $message [description]
     *
     * @return [type] [description]
     */
    public function sendAll($message = null , $action = null, $data = [], $status = 'ok', $error_code = null, $error_data = null)
    {
        foreach ($this->clients as $client) {

          if ($client->user->user_status == 'offline')
          {
              continue;
          }

          $this->sendJSONResponse(
            $client,
            $status,
            $action,
            $data,
            $message,
            $error_code,
            $error_data,
            $context = null
          );
            //$client->send($message);
        }
    }

}