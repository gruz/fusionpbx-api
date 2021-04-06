<?php

namespace Api\User\Controllers;

use Illuminate\Http\Request;
use Api\User\Services\UserService;
use Api\Domain\Services\TeamService;
use Infrastructure\Http\Controller;
use Api\User\Requests\SignupRequest;
use Api\User\Services\UserPasswordService;
use Api\User\Requests\CreateUserRequest;
use Api\User\Requests\UserGroupsRequest;
use Api\User\Requests\UserResetPasswordRequest;
use Api\User\Requests\UserForgotPasswordRequest;
use Api\User\Requests\UserUpdatePasswordRequest;
use Api\User\Requests\SignupUserRequest;

/**
 * @OA\Schema()
 */
class UserController extends Controller
{
    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var TeamService
     */
    private $teamService;

    /**
     * @var UserPasswordService
     */
    private $passwordService;

    public function __construct(
        UserService $userService,
        TeamService $teamService,
        UserPasswordService $passwordService
    ) {
        $this->userService = $userService;
        $this->teamService = $teamService;
        $this->passwordService = $passwordService;
    }

    /**
     * Get user list in domain
     *
     * `TODO`, describe in docs and return only some fields available for other users,
     * add parameters in query to select contact info, extension
     *
    @OA\Get(
        tags={"User"},
        path="/users",
        @OA\Parameter(
            description="Relations to be attached",
            allowReserved=true,
            name="includes[]",
            in="query",
            @OA\Schema(
                type="array",
                @OA\Items(type="string",
                    enum = { "groups", "status", "domain", "permissions", "emails","extensions", },
                )
            )
        ),
        @OA\Response(
            response=200,
            description="`TODO Stub` Could not created domain",
            @OA\JsonContent(type="array",
                @OA\Items(ref="#/components/schemas/UserWithRelatedItemsSchema"),
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
    @OA\Get(
        tags={"User"},
        path="/user/{user_uuid}",
        @OA\Parameter(ref="#/components/parameters/user_uuid"),
        @OA\Parameter(
            description="Relations to be attached",
            allowReserved=true,
            name="includes[]",
            in="query",
            @OA\Schema(ref="#/components/schemas/user_includes")
        ),
        @OA\Response(response=200, description="`TODO Stub` Success ..."),
        @OA\Response(response=400, description="`TODO Stub` Could not ..."),
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
    @OA\Get(
        tags={"User"},
        path="/user",
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
     * Creates a user inside a domain
     *
    @OA\Post(
        tags={"User"},
        path="/user",
        @OA\RequestBody(
            description="User information",
            required=true,
            @OA\JsonContent(
                ref="#/components/schemas/UserCreateSchema",
                examples={
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
            description="`TODO Stub` Could not created domain",
            @OA\JsonContent(ref="#/components/schemas/User"),
        ),
        @OA\Response(response=400, description="`TODO Stub` Could not ..."),
    )
     */
    public function create(CreateUserRequest $request)
    {
        $data = $request->get('user', []);

        return $this->response($this->userService->create($data), 201);
    }


    /**
     * Update oneself or another user if having enoght permissions
     *
    @OA\Put(
        tags={"User"},
        path="/user/{user_uuid}",
        @OA\Parameter(ref="#/components/parameters/user_uuid"),
        @OA\RequestBody(
            description="User information",
            required=true,
            @OA\JsonContent(
                ref="#/components/schemas/UserCreateSchema",
                examples={
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
        @OA\Response(response=200, description="`TODO Stub` Success ..."),
        @OA\Response(response=400, description="`TODO Stub` Could not ..."),
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
    public function activate(string $hash)
    {
        $response = $this->response($this->userService->activate($hash));

        return $response;
    }

    /**
     * Delete a domain `TODO descendant delete user with extenions, contacts, handle last domain admin delition`
     *
     * Not implemented yet
     *
    @OA\Delete(
        tags={"User"},
        path="/user/{user_uuid}",
        @OA\Parameter(ref="#/components/parameters/user_uuid"),
        @OA\Response(response=200, description="`TODO Stub` Success ..."),
        @OA\Response(response=400, description="`TODO Stub` Could not ..."),
    )
     */
    public function delete($userId)
    {
        return $this->response($this->userService->delete($userId));
    }

    public function addGroups($userId, UserGroupsRequest $request)
    {
        $groups = $request->get('groups', []);

        return $this->response($this->userService->addGroups($userId, $groups));
    }

    public function setGroups($userId, UserGroupsRequest $request)
    {
        $groups = $request->get('groups', []);

        return $this->response($this->userService->setGroups($userId, $groups));
    }

    public function removeGroups($userId, UserGroupsRequest $request)
    {
        $groups = $request->get('groups', []);

        return $this->response($this->userService->removeGroups($userId, $groups));
    }

    // ~ public function create(CreateUserRequest $request)
    // ~ {
    // ~ $data = $request->get('user', []);

    // ~ return $this->response($this->userService->create($data), 201);
    // ~ }

    /**
     *
     * User signup
     *
     * Signup a user to domain. A user can have several extensions, several contacts and a bunch of settings.
     *
     * @param SignupRequest $request
     * @return void
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
                examples={
                    "Create a user": {},
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
    public function signup(SignupUserRequest $request)
    {
        dd('here');
        $data = $request->get('user', []);

        return $this->response($this->userService->create($data), 201);
    }


    public function signupDEL(SignupRequest $request)
    {
        $data = $request->get('team', []);

        if (empty($data)) {
            $data = $request->get('user', []);

            $data['isTeam'] = false;
            $data['group_name'] = env('DEFAULT_USER_GROUP_NAME');

            // Since there is no a field dedicated to activation, Gruz have decided to use the quazi-boolean user_enabled field.
            // FusionPBX recognizes non 'true' as FALSE. So our hash in the user_enabled field is treated as FALSE till user is activated.
            $data['user_enabled'] = md5(uniqid() . microtime());

            return $this->response($this->userService->create($data, false), 201);
        }

        $data['isTeam'] = true;
        $data['user_enabled'] = 'true';
        $data['group_name'] = env('MOTHERSHIP_DOMAIN_DEFAULT_GROUP_NAME');

        return $this->response($this->teamService->createDeperacted($data), 201);
    }

    /**
     * User forgot password 
     *
     * @param UserForgotPasswordRequest $request
     * @return void
     * 
    @OA\Post(
        tags={"User"},
        path="/forgot-password",
        @OA\RequestBody(
            description="User information to reset his password",
            required=true,
            @OA\JsonContent(
                ref="#/components/schemas/UserForgotPasswordSchema",
                examples={
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
                examples={
                    "Password resent link basic example1": {
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
    public function forgotPassword(UserForgotPasswordRequest $request)
    {
        $data = $request->only('user_email', 'domain_name');

        return $this->response($this->passwordService->generateResetToken($data));
    }

    /**
     * User get reset password action
     * 
     * @param UserResetPasswordRequest $request
     * @return void
     */
    public function resetPassword(UserResetPasswordRequest $request)
    {
        return view('user.password.reset-password', [
            'token' => $request->get('token'),
            'email' => $request->get('email'),
            'domain_name' => $request->get('domain_name')
        ]);
    }

    /**
     * User reset password after form submission
     *
     * @param UserUpdatePasswordRequest $request
     * @return void
     * 
     @OA\Post(
        tags={"User"},
        path="/update-password",
        @OA\RequestBody(
            description="Update user new password request",
            required=true,
            @OA\JsonContent(
                ref="#/components/schemas/UserUpdatePasswordSchema",
                examples={
                    "Set new user password basic example": {
                        "summary": "Set new user password basic example",
                        "value": {
                            "token":"0e07a67a80460d08b72fa6e88703586668455d70afef08e51ef8ce3bdf9fe8a8",
                            "password":"my_secure_password",
                            "password_confirmation":"my_secure_password",
                            "user_email":"your_user@email.com"
                        }
                    },
                }
            )
        ),
        @OA\Response(
            response=200,
            description="Update user password response",
            @OA\JsonContent(
                @OA\Schema(@OA\Property(
                    property="success",
                    type="string",
                    description="Update user password response" 
                )),
                example={
                    "success": "User password has been successfully set",
                }
            ),
        ),
        @OA\Response(
            response=422,
            description="Validation error - ...",
        ),
        @OA\Response(
            response=400,
            description="Could not ...",
        ),
    )
     */
    public function updatePassword(UserUpdatePasswordRequest $request)
    {
        $data = $request->only(
            'user_email',
            'password',
            'password_confirmation',
            'token',
            'domain_name'
        );

        $status = $this->passwordService->resetPassword($data);

        if ($status === null)
            return back()->withErrors(['password' => __('Invalid data')]);

        return view('user.password.reset-success');
    }
}
