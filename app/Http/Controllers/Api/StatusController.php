<?php

namespace App\Http\Controllers\Api;

use App\Requests\SetStatusRequest;
use App\Services\Fpbx\StatusService;

class StatusController extends AbstractBrunoController
{
    private $controllerService;

    public function __construct(StatusService $controllerService)
    {
        $this->controllerService = $controllerService;

        $this->nameSpaceHelper = get_class($this);
        $this->nameSpaceHelper = explode('Controller', $this->nameSpaceHelper, 2);
        $this->nameSpaceHelper = strtolower($this->nameSpaceHelper[0]);
    }

    public function setStatus(SetStatusRequest $request)
    {
        // ~ $data = $request->get($this->nameSpace, []);
        $data = $request->all();

        $response = $this->controllerService->setStatus($data);

        return $this->response($response, 201);
    }
}
