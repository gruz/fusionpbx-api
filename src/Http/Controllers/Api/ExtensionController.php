<?php

namespace Gruz\FPBX\Http\Controllers\Api;

use Gruz\FPBX\Services\Fpbx\ExtensionService;
use Gruz\FPBX\Requests\CreateExtensionRequest;
use Gruz\FPBX\Requests\UpdateExtensionRequest;

/**
 * @OA\Schema()
 */
class ExtensionController extends AbstractBrunoController
{
    private $extensionService;

    public function __construct(ExtensionService $extensionService)
    {
        $this->extensionService = $extensionService;
    }

    /**
     * Get all extensions in domain
     *
     * `TODO`, describe in docs and return only some fields available for other users,
     * add parameters in query to select contact info, extension
     *
    @OA\Get(
        tags={"Extension"},
        path="/extensions",
        @OA\Parameter(
            description="Relations to be attached",
            allowReserved=true,
            name="includes[]",
            in="query",
            @OA\Schema(
                type="array",
                @OA\Items(type="string",
                    enum = { "users" },
                )
            )
        ),
        @OA\Response(
            response=200,
            description="`TODO Stub` Could not created domain",
            @OA\JsonContent(type="array",
                @OA\Items(ref="#/components/schemas/ExtensionWithRelatedUsersSchema"),
            ),
        ),
    )
     */
    public function getAll()
    {
        $resourceOptions = $this->parseResourceOptions();

        $data = $this->extensionService->getAll($resourceOptions);
        $parsedData = $this->parseData($data, $resourceOptions, 'extensions');

        return $this->response($parsedData);
    }

    /**
     * Get extension by ID
     *
    @OA\Get(
        tags={"Extension"},
        path="/extension/{extension_uuid}",
        @OA\Parameter(ref="#/components/parameters/extension_uuid"),
        @OA\Parameter(
            description="Relations to be attached",
            allowReserved=true,
            name="includes[]",
            in="query",
            @OA\Schema(
                type="array",
                @OA\Items(type="string",
                    enum = { "users" },
                )
            )
        ),
        @OA\Response(
            response=200,
            description="`TODO Stub`",
            @OA\JsonContent(ref="#/components/schemas/ExtensionWithRelatedUsersSchema"),
        ),
        @OA\Response(response=400, description="`TODO Stub` Could not ..."),
    )
    */
    public function getById(string $extensionId)
    {
        $resourceOptions = $this->parseResourceOptions();

        $data = $this->extensionService->getById($extensionId, $resourceOptions);
        $parsedData = $this->parseData($data, $resourceOptions, 'extension');

        return $this->response($parsedData);
    }

    /**
     * Extension create
     *
     * Creates an extension and attaches it to a user (optionally)
     *
    @OA\Post(
        tags={"Extension"},
        path="/extension",
        @OA\RequestBody(
            required=true,
            @OA\JsonContent(
                allOf={
                    @OA\Schema(ref="#/components/schemas/Extension"),
                    @OA\Schema(@OA\Property(
                        property="users",
                        type="array",
                        @OA\Items(
                            allOf={
                                @OA\Schema(
                                    @OA\Property(
                                        property="user_uuid",
                                        type="string",
                                        format="uuid",
                                        description="User id's to attach extension to. If not passed, then extension is attached to the current user",
                                    )
                                ),
                            }
                        ),
                    )),
                },
                example={
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

    /**
     * Updates an extension
     *
    @OA\Put(
        tags={"Extension"},
        path="/extension/{extension_uuid}",
        @OA\Parameter(ref="#/components/parameters/extension_uuid"),
        @OA\RequestBody(
            required=true,
            @OA\JsonContent(
                ref="#/components/schemas/ExtensionCreateSchema",
                example={
                    "Create a user": {},
                    "Create a user basic example": {
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
            description="`TODO Stub` Success ...",
            @OA\JsonContent(ref="#/components/schemas/Extension"),
        ),
        @OA\Response(response=400, description="`TODO Stub` Could not ..."),
    )
    */
    public function update($extensionId, UpdateExtensionRequest $request)
    {
        $data = $request->get('extension', []);

        $update = $this->extensionService->update($extensionId, $data);

        $return = $this->response($update);

        return $return;
    }

    /**
     * Delets an extension
     *
    @OA\Delete(
        tags={"Extension"},
        path="/extension/{extension_uuid}",
        @OA\Parameter(ref="#/components/parameters/extension_uuid"),
        @OA\Response(
            response=200,
            description="`TODO Stub` Success ...",
            @OA\JsonContent(
                example={
                    "messages": {
                        "`TODO` Describe response",
                    },
                },
            ),
        ),
        @OA\Response(response=400, description="`TODO Stub` Could not ..."),
    )
    */
    public function delete($extensionId)
    {
        return $this->response($this->extensionService->delete($extensionId));
    }

}
