<?php

namespace Infrastructure\Events;

use Infrastructure\Events\Event;

class CGRTFailedEvent extends Event
{
    public $request;

    public $response;

    public function __construct($request, $response)
    {
        $this->request = $request;
        $this->response = $response;
    }
}
