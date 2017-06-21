<?php

namespace Api\Status\Controllers;

use Illuminate\Http\Request;
use App\Http\Controller;
use Api\Status\Requests\SetStatusRequest;
use Api\Status\Services\StatusService;

class StatusController extends Controller
{
    private $controllerService;

    private $nameSpace = 'status';

    public function __construct(StatusService $controllerService)
    {
        $this->controllerService = $controllerService;

        $this->nameSpaceHelper = get_class($this);
        $this->nameSpaceHelper = explode('Controller', $this->nameSpaceHelper, 2);
        $this->nameSpaceHelper = strtolower($this->nameSpaceHelper[0]);
    }

    public function set(SetStatusRequest $request)
    {
        $data = $request->get($this->nameSpace, []);

        return $this->response($this->controllerService->set($data), 201);
    }
}
