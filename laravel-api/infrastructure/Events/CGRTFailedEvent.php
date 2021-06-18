<?php

namespace Infrastructure\Events;

use Infrastructure\Events\Event;

class CGRTFailedEvent extends Event
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
