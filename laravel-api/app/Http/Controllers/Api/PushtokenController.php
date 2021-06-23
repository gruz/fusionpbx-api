<?php

namespace App\Http\Controllers\Api;

use App\Services\PushtokenService;
use App\Requests\CreatePushtokenRequest;

class PushtokenController extends AbstractBrunoController
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
        // ~ $data = $request->get($this->nameSpace, []);
        $data = $request->all();

        return $this->response($this->controllerService->create($data), 201);
    }
}
