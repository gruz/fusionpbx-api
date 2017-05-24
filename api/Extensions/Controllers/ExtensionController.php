<?php

namespace Api\Extensions\Controllers;

use Illuminate\Http\Request;
use Infrastructure\Http\Controller;
use Api\Extensions\Requests\CreateExtensionRequest;
use Api\Extensions\Services\ExtensionService;

class ExtensionController extends Controller
{
    private $extensionService;

    public function __construct(ExtensionService $extensionService)
    {
        $this->extensionService = $extensionService;
    }

    public function getAll()
    {
        $resourceOptions = $this->parseResourceOptions();

        $data = $this->extensionService->getAll($resourceOptions);
        $parsedData = $this->parseData($data, $resourceOptions, 'extensions');

        return $this->response($parsedData);
    }

    public function getById($extensionId)
    {
        $resourceOptions = $this->parseResourceOptions();

        $data = $this->extensionService->getById($extensionId, $resourceOptions);
        $parsedData = $this->parseData($data, $resourceOptions, 'extension');

        return $this->response($parsedData);
    }

    public function create(CreateExtensionRequest $request)
    {
        $data = $request->get('extension', []);

        return $this->response($this->extensionService->create($data), 201);
    }

    public function update($extensionId, Request $request)
    {
        $data = $request->get('extension', []);

        return $this->response($this->extensionService->update($extensionId, $data));
    }

    public function delete($extensionId)
    {
        return $this->response($this->extensionService->delete($extensionId));
    }

}
