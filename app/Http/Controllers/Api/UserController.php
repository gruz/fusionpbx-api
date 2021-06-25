<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Services\Fpbx\UserService;
use App\Requests\CreateUserRequest;
use App\Requests\UserActivateRequest;
use App\Services\UserPasswordService;
use App\Requests\UserSignupRequestApi;
use App\Requests\UserForgotPasswordRequestApi;

/**
 * @OA\Schema()
 */
class UserController extends AbstractBrunoController
{
    /**
     * @var UserService
     */
    private $userService;

    public function __construct(
        UserService $userService
    ) {
        $this->userService = $userService;
    }

    /**
     * Get user list in domain
     *
     * `TODO`, describe in docs and return only some fields available for other users,
     * add parameters in query to select contact info, extension
     *
    @ OA\Get(
        tags={"User"},
        path="/users",
        @ OA\Parameter(
            description="Relations to be attached",
            allowReserved=true,
            name="includes[]",
            in="query",
            @ OA\Schema(
                type="array",
                @ OA\Items(type="string",
                    enum = { "groups", "status", "domain", "permissions", "emails","extensions", },
                )
            )
        ),
        @ OA\Response(
            response=200,
            description="`TODO Stub` Could not created domain",
            @ OA\JsonContent(type="array",
                @ OA\Items(ref="#/components/schemas/UserWithRelatedItemsSchema"),
            ),
        ),
        security={{"bearer_auth": {}}}
    )
     */
    public function getAll()
    {
        $resourceOptions = $this->parseResourceOptions();

        $data = $this->userService->getAll($resourceOptions);
        $parsedData = $this->parseData($data, $resourceOptions, 'users');

        return $this->response($parsedData);
    }

    /**
     * Get user info by ID
     *
    @ OA\Get(
        tags={"User"},
        path="/user/{user_uuid}",
        @ OA\Parameter(ref="#/components/parameters/user_uuid"),
        @ OA\Parameter(
            description="Relations to be attached",
            allowReserved=true,
            name="includes[]",
            in="query",
            @ OA\Schema(ref="#/components/schemas/user_includes")
        ),
        @ OA\Response(response=200, description="`TODO Stub` Success ..."),
        @ OA\Response(response=400, description="`TODO Stub` Could not ..."),
    )
     */
    public function getById(string $userId)
    {
        $resourceOptions = $this->parseResourceOptions();

        $data = $this->userService->getById($userId, $resourceOptions);
        $parsedData = $this->parseData($data, $resourceOptions, null);

        return $this->response($parsedData);
    }

    /**
     * Gets currently logged in user info
     *
    @ OA\Get(
        tags={"User"},
        path="/user",
        @ OA\Parameter(
            description="Relations to be attached",
            allowReserved=true,
            name="includes[]",
            in="query",
            @ OA\Schema(ref="#/components/schemas/user_includes")
        ),
        @ OA\Response(
            response=200,
            description="`TODO Stub` Could not created domain",
            @ OA\JsonContent(ref="#/components/schemas/UserWithRelatedItemsSchema"),
        ),
        @ OA\Response(response=400, description="`TODO Stub` Could not ..."),
    )
     */
    public function getMe()
    {
        $resourceOptions = $this->parseResourceOptions();

        $data = $this->userService->getMe($resourceOptions);
        $parsedData = $this->parseData($data, $resourceOptions, null);

        return $this->response($parsedData);
    }


    /**
     * Creates a user inside a domain by a user with permissions to create. It's not a signup!
     *
    @ OA\Post(
        tags={"User"},
        path="/user",
        @ OA\RequestBody(
            description="User information",
            required=true,
            @ OA\JsonContent(
                ref="#/components/schemas/UserCreateSchema",
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
        @ OA\Response(
            response=200,
            description="`TODO Stub` Could not created domain",
            @ OA\JsonContent(ref="#/components/schemas/User"),
        ),
        @ OA\Response(response=400, description="`TODO Stub` Could not ..."),
    )
     */
    public function create(CreateUserRequest $request)
    {
        $data = $request->get('user', []);

        return $this->response($this->userService->create($data), 201);
    }


    /**
     * Update oneself or another user if having enough permissions
     *
    @ OA\Put(
        tags={"User"},
        path="/user/{user_uuid}",
        @ OA\Parameter(ref="#/components/parameters/user_uuid"),
        @ OA\RequestBody(
            description="User information",
            required=true,
            @ OA\JsonContent(
                ref="#/components/schemas/UserCreateSchema",
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
        @ OA\Response(response=200, description="`TODO Stub` Success ..."),
        @ OA\Response(response=400, description="`TODO Stub` Could not ..."),
    )
     */
    public function update($userId, Request $request)
    {
        $data = $request->get('user', []);

        return $this->response($this->userService->update($userId, $data));
    }

    /**
     * Activate user by email link. In cases it's and admin user, activate domain as well
     *
    @OA\Get(
        tags={"User"},
        path="/user/activate/{hash}",
        x={"route-$path"="fpbx.user.activate"},
        @OA\Parameter(
            name="hash",
            in="path",
            description="User activation link",
            required=true,
            @OA\Schema(
                type="string",
                format="uuid",
                example="541f8e60-5ae0-11eb-bb80-b31e63f668c8",
            )
        ),
        @OA\Response(response=200, description="`TODO Stub` Success ..."),
        @OA\Response(response=400, description="`TODO Stub` Could not ..."),
    )
     */
    public function activate(string $hash, UserActivateRequest $request)
    {
        $response = $this->response($this->userService->activate($hash));

        return $response;
    }

    /**
     * Delete a domain `TODO descendant delete user with extenions, contacts, handle last domain admin delition`
     *
     * Not implemented yet
     *
    @ OA\Delete(
        tags={"User"},
        path="/user/{user_uuid}",
        @ OA\Parameter(ref="#/components/parameters/user_uuid"),
        @ OA\Response(response=200, description="`TODO Stub` Success ..."),
        @ OA\Response(response=400, description="`TODO Stub` Could not ..."),
    )
     */
    public function delete($userId)
    {
        return $this->response($this->userService->delete($userId));
    }

    /**
     *
     * User signup
     *
     * Signup a user to domain. A user can have several extensions, several contacts and a bunch of settings.
     *
    @OA\Post(
        tags={"User"},
        path="/user/signup",
        x={"route-$path"="fpbx.user.signup"},
        @OA\RequestBody(
            description="User information",
            required=true,
            @OA\JsonContent(
                ref="#/components/schemas/UserCreateSchema",
                example={
                    "Create a user": {}
                }
            ),
        ),
        @OA\Response(
            response=200,
            description="Domain created response",
            @OA\JsonContent(
                allOf={
                    @OA\Schema(ref="#/components/schemas/DomainCreateSchema"),
                }
            ),
        ),
    )
     */
    public function signup(UserSignupRequestApi $request)
    {
        $data = $request->except('domain_uuid');

        return $this->response($this->userService->create($data), 201);
    }

    /**
     * User forgot password - request email link to reset password
     *
    @OA\Post(
        tags={"User"},
        path="/forgot-password",
        x={"route-$path"="fpbx.user.forgot-password"},
        @OA\RequestBody(
            description="User information to reset his password",
            required=true,
            @OA\JsonContent(
                ref="#/components/schemas/UserForgotPasswordSchema",
                example={
                    "Request email with link basic example": {
                        "summary": "Request email with link basic example",
                        "value": {
                            "user_email":"your_user@email.com",
                            "domain_name":"jimmie.biz"
                        }
                    },
                }
            )
        ),
        @OA\Response(
            response=200,
            description="Password resent link response",
            @OA\JsonContent(
                ref="#/components/schemas/UserCreateSchema",
                example={
                    "Password resent link basic example": {
                        "username": "user_Destany.Windler",
                        "domain_uuid": "142ce990-6e16-11eb-8ad7-99f61fb0e7c6"
                    },
                }
            ),
        ),
        @OA\Response(
            response=422,
            description="Validation error - empty email",
            @OA\JsonContent(
                type="object",
                @OA\Property(
                    property="errors",
                    type="array",
                    example={{
                        "status": "422",
                        "code": 0,
                        "title": "Validation error",
                        "detail": "The user email is required."
                    }},
                    @OA\Items(
                        @OA\Property(
                          property="status",
                          type="string",
                          example="422"
                       ),
                       @OA\Property(
                          property="code",
                          type="number",
                          example=0
                       ),
                       @OA\Property(
                          property="title",
                          type="string",
                          example="Validation error"
                       ),
                       @OA\Property(
                          property="detail",
                          type="string",
                          example="The user email is required."
                       ),
                    ),
                )
            ),
        ),
    )
     */
    public function forgotPassword(UserForgotPasswordRequestApi $request, UserPasswordService $userPasswordService)
    {
        $data = $request->only('user_email', 'domain_name');

        return $this->response($userPasswordService->generateResetToken($data));
    }
}
