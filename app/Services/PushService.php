<?php

namespace App\Services;

use App\Models\User;
use App\Exceptions\UserNotFoundException;
use App\Exceptions\DomainNotFoundException;
use App\Repositories\DomainRepository;
use App\Exceptions\WrongDestinationException;

class PushService
{
    private $domainRepository;

    public function __construct(
        DomainRepository $domainRepository
    ) {
        $this->domainRepository = $domainRepository;
    }

    public function pushUser($destination)
    {
        $destination = explode('@', $destination);

        if (count($destination) != 2) {
            $destination = implode('@', $destination);
            throw new WrongDestinationException($destination);
        }

        $extension = $destination[0];
        $domain_name = $destination[1];

        // Check if domain exists
        $domain = $this->domainRepository->getWhere('domain_name', $domain_name);

        // We cannot create a user if there is not such a domain
        if ($domain->count() < 1) {
            throw new DomainNotFoundException;
        }

        $domain = $domain->first();

        // ~ $admins = User::where([
        $users = User::where([
            'domain_uuid' => $domain->domain_uuid,
            'user_enabled' => 'true'
        ])->whereHas('extensions', function ($query) use ($extension) {
            $query->where('extension', $extension);
        })
            ->has('extensions')
            ->has('pushtokens')
            ->with(['extensions', 'pushtokens'])

            ->get();

        if ($users->count() < 1) {
            throw new UserNotFoundException;
        }

        foreach ($users as $user) {
            foreach ($user->pushtokens as $pushtoken) {
                $pushtoken->username = $user->username;
                $this->push($pushtoken);
            }
        }
    }

    public function push($pushtoken)
    {
        $message = $pushtoken->username . ': ' . __(env('VOIP_MESSAGE', __('Hey, wake up!'))) . '(userid: ' . $pushtoken->user_uuid . ')';

        // Put the full path to your .pem file
        // Put your alert message here:
        $pemFile = base_path() . '/' . env('VOIP_APPLE_CERT_PATH', false);

        $token = preg_replace("/[^0-9a-zA-Z]/", "", $pushtoken->token);

        ////////////////////////////////////////////////////////////////////////////////

        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', $pemFile);

        // Put your private key's passphrase here:
        $passphrase = env('VOIP_APPLE_CERT_PASSPHRASE', false);

        if ($passphrase) {
            stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
        }

        switch ($pushtoken->token_type) {
            case 'sandbox':
                $server = 'sandbox.';
                break;
            default:
                $server = '';
                break;
        }

        // Open a connection to the APNS server
        $fp = stream_socket_client(
            'ssl://gateway.' . $server . 'push.apple.com:2195',
            $err,
            $errstr,
            60,
            STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT,
            $ctx
        );

        if (!$fp)
            exit("Failed to connect: $err $errstr" . PHP_EOL);

        echo 'Connected to APNS' . PHP_EOL;

        // Create the payload body
        $body['aps'] = array(
            'alert' => $message,
            'sound' => 'default'
        );

        // Encode the payload as JSON
        $payload = json_encode($body);

        // Build the binary notification
        $msg = chr(0) . pack('n', 32)
            . pack('H*', $token)
            . pack('n', strlen($payload))
            . $payload;
        // Send it to the server
        $result = fwrite($fp, $msg, strlen($msg));
        if (!$result)
            echo 'Message not delivered' . PHP_EOL;
        else
            echo 'Message successfully delivered' . PHP_EOL;
        // Close the connection to the server
        fclose($fp);

        file_put_contents('/tmp/log.txt', print_r($body, true));
    }
}
