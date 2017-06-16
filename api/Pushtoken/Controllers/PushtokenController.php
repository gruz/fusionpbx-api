<?php

namespace Api\Pushtoken\Controllers;

use Illuminate\Http\Request;
use App\Http\Controller;
use Api\Pushtoken\Requests\CreatePushtokenRequest;
use Api\Pushtoken\Services\PushtokenService;

class PushtokenController extends Controller
{
    private $controllerService;

    private $nameSpace = 'pushtoken';

    public function __construct(PushtokenService $controllerService)
    {
        $this->controllerService = $controllerService;

        $this->nameSpaceHelper = get_class($this);
        $this->nameSpaceHelper = explode('Controller', $this->nameSpaceHelper, 2);
        $this->nameSpaceHelper = strtolower($this->nameSpaceHelper[0]);
    }

    public function create(CreatePushtokenRequest $request)
    {
        $data = $request->get($this->nameSpace, []);

        return $this->response($this->controllerService->create($data), 201);
    }
}
