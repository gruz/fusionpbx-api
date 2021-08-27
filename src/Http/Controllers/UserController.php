<?php

namespace Gruz\FPBX\Http\Controllers;

use Illuminate\Http\Request;
use Gruz\FPBX\Requests\UserLoginRequest;
use Gruz\FPBX\Services\Fpbx\UserService;
use Gruz\FPBX\Requests\CreateUserRequest;
use Illuminate\Support\Facades\Hash;
use Gruz\FPBX\Requests\UserActivateRequest;
use Gruz\FPBX\Services\UserPasswordService;
use Gruz\FPBX\Requests\UserSignupRequestApi;
use Gruz\FPBX\Requests\UserForgotPasswordRequestApi;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

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
            @OA\MediaType(
                mediaType="application/json",
                @OA\Schema(ref="#/components/schemas/UserCreateSchema"),
                @OA\Examples(example=200, summary="", value={"username":"alyson3.dietrich@howe.com","add_user":"admin","domain_uuid":"8cffb9b5-41a4-4dfe-9ae5-619a4394634f","add_date":"2021-08-25 07:21:12.125369+0000","user_enabled":"f6b78951340bd4813ea5a5a275e08d1220ad51c7","user_uuid":"a935de0c-539d-4443-8036-a8120aedda01","domain":{"domain_uuid":"8cffb9b5-41a4-4dfe-9ae5-619a4394634f","domain_parent_uuid":null,"domain_name":"mertz12.com","domain_enabled":true,"domain_description":"Created via Factory during tests"}}),
                @OA\Examples(example=300, summary="", value={"name":1}),
                @OA\Examples(example=400, summary="", value={"name":1})
            )
        ),
        @OA\Response(
            response=200,
            description="User created response",
            @OA\MediaType(
                mediaType="application/json",
                @OA\Examples(example="200", summary="Success", value={"username":"alyson3.dietrich@howe.com","add_user":"admin","domain_uuid":"8cffb9b5-41a4-4dfe-9ae5-619a4394634f","add_date":"2021-08-25 07:21:12.125369+0000","user_enabled":"f6b78951340bd4813ea5a5a275e08d1220ad51c7","user_uuid":"a935de0c-539d-4443-8036-a8120aedda01","domain":{"domain_uuid":"8cffb9b5-41a4-4dfe-9ae5-619a4394634f","domain_parent_uuid":null,"domain_name":"mertz12.com","domain_enabled":true,"domain_description":"Created via Factory during tests"}}),
            )
        ),

        @OA\Response(
            description="Application name and version",
            response=422,
            @OA\MediaType(
                mediaType="application/json",
                @OA\Examples(example="200", summary="User already exists", value={"errors":{{"status":"422","code":422,"title":"Validation error","detail":"The user email has already been taken."},{"status":"422","code":422,"title":"Validation error","detail":"The username has already been taken."},{"status":"422","code":422,"title":"Validation error","detail":"The extensions.0.extension has already been taken."}}}),
            )
        ),
    )
     */
    public function signup(UserSignupRequestApi $request)
    {
        $data = $request->except('domain_uuid');

        return $this->response($this->userService->create($data), 201);
    }

    /**
     * Activate user by email link. In cases it's and admin user, activate domain as well
     *
    @OA\Get(
        tags={"User"},
        path="/verify-email/{id}/{hash}",
        x={"route-$path"="verification.verify"},
        @OA\Parameter(
            name="id",
            in="path",
            description="User uuid passed to the verification email",
            required=true,
            @OA\Schema(
                type="string",
                format="uuid",
                example="973add20-16b8-467d-ad02-42ffd1cc4aa4",
            )
        ),
        @OA\Parameter(
            name="hash",
            in="path",
            description="User activation hash from verification emails",
            required=true,
            @OA\Schema(
                type="string",
                example="65db8e98584c1d9a83b1b64371d157049f470d75",
            )
        ),
        @OA\Response(
            description="Application name and version",
            response=200,
            @OA\MediaType(
                mediaType="application/json",
                @OA\Examples(example="200", summary="Success", value={"message":"User activated","user":{"user_uuid":"973add20-16b8-467d-ad02-42ffd1cc4aa4","domain_uuid":"8c292d13-0e70-4a08-8f50-eb8bb4348ae4","username":"marisa36@watsica.net","contact_uuid":null,"api_key":null,"user_enabled":"true","add_user":"admin","add_date":"2021-08-23 17:15:21.262935+0000","account_code":null}}),
            )
        ),
    )
     */
    public function activate(string $id, string $hash, UserActivateRequest $request, UserService $userService)
    {
        $response = $this->response($userService->activate($hash));

        return $response;
    }


    /**
     * Gets currently logged in user info
     *
    @OA\Get(
        tags={"User"},
        path="/user",
        security={{"bearer_auth": {}}},
        x={
            "route-$path"="fpbx.user.own",
            "route-$middlewares"="api,auth:sanctum"
        },
        @OA\Parameter(
            description="Relations to be attached",
            allowReserved=true,
            name="includes[]",
            in="query",
            @OA\Schema(ref="#/components/schemas/user_includes")
        ),
        @OA\Response(
            response=200,
            description="`TODO Stub` Could not created domain",
            @OA\JsonContent(ref="#/components/schemas/UserWithRelatedItemsSchema"),
        ),
        @OA\Response(response=400, description="`TODO Stub` Could not ..."),
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
     * User forgot password - request email link to reset password
     *
    @OA\Post(
        tags={"User"},
        path="/user/forgot-password",
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

    /**
     *
     * User login
     *
     * Login user and return access token
     *
    @OA\Post(
        tags={"User"},
        path="/user/login",
        @OA\RequestBody(
            description="User information",
            required=true,
            @OA\JsonContent(
                example={
                    "domain_name" : "192.168.0.160",
                    "username" : "admin",
                    "password" : "admin"
                }
            ),
        ),
        @OA\Response(
            response=200,
            description="Login successfull",
            @OA\JsonContent(
                example={
                    "access_token": "18|o7yAzJLTcRFECUUrBERn44ITisQTGDDJUY1KHdJ0"
                }
            ),
        ),
        @OA\Response(
            response=422,
            description="Bad domain",
            @OA\JsonContent(
                example={
                    "errors": {
                        {
                            "status": "422",
                            "code": 422,
                            "title": "Validation error",
                            "detail": "The selected domain name is invalid."
                        }
                    }
                }
            ),
        ),
        @OA\Response(
            response=401,
            description="Invalid credentials.",
            @OA\JsonContent(
                example={
                    "status": "error",
                    "code": 401,
                    "message": "Invalid credentials.",
                }
            ),
        ),
    )
     */
    public function login(UserLoginRequest $request, UserService $userService)
    {
        $user = $userService->getUserByUsernameAndDomain($request->get('username'), $request->get('domain_name'));

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw new UnauthorizedHttpException('Basic', __('Invalid credentials.'), null, 401);
            // throw ValidationException::withMessages([
            //     'username' => ['The provided credentials are incorrect.'],
            // ]);
        }

        $token = $user->createToken($request->username)->plainTextToken;

        return $this->response(['access_token' => $token]);
    }
}
