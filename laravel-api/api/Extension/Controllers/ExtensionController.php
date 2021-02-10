<?php

namespace Api\Extension\Controllers;

use Illuminate\Http\Request;
use Infrastructure\Http\Controller;
use Api\Extension\Requests\CreateExtensionRequest;
use Api\Extension\Requests\UpdateExtensionRequest;
use Api\Extension\Services\ExtensionService;

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

    public function getById(string $extensionId)
    {
        $resourceOptions = $this->parseResourceOptions();

        $data = $this->extensionService->getById($extensionId, $resourceOptions);
        $parsedData = $this->parseData($data, $resourceOptions, 'extension');

        return $this->response($parsedData);
    }

    /**
    @OA\Post(
        tags={"Extension"},
        path="/extension",
        summary="Extension create",
        description="Creates an extension and attaches it to a user (optionally)",
        @OA\RequestBody(
            required=true,
            @OA\JsonContent(
                ref="#/components/schemas/Extension",
                examples={
                    "Create an exnetsion": {},
                    "Create an exnetsion basic example": {
                        "summary" : "`TODO example`",
                        "value": {
                            "code": 403,
                            "message": "登录失败",
                            "data": null
                        }
                    },
                }
            ),
        ),
        @OA\Response(
            response=200,
            description="Extension created response",
            @OA\JsonContent(ref="#/components/schemas/Extension"),
        ),
        @OA\Response(
            response=400,
            description="`TODO Stub` Could not created domain",
            @OA\JsonContent(
                example={
                    "messages": {
                        "Missing admin user",
                        "No password for email",
                    },
                },
            ),
        ),
    )
     */
    public function create(CreateExtensionRequest $request)
    {
        $data = $request->get('extension', []);

        return $this->response($this->extensionService->create($data), 201);
    }

    public function update($extensionId, UpdateExtensionRequest $request)
    {
        $data = $request->get('extension', []);

        $update = $this->extensionService->update($extensionId, $data);

        $return = $this->response($update);

        return $return;
    }

    public function delete($extensionId)
    {
        return $this->response($this->extensionService->delete($extensionId));
    }

}
