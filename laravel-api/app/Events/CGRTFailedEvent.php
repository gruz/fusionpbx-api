<?php

namespace App\Events;

class CGRTFailedEvent extends AbstractEvent
{
    public $request;

    public $response;

    public $userData;

    public function __construct($request, $response, $userData = [])
    {
        $this->request = $request;
        $this->response = $response;
        $this->userData = $userData;
    }
}
