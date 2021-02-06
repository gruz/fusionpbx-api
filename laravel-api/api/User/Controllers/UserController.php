<?php

namespace Api\User\Controllers;

use Illuminate\Http\Request;
use Infrastructure\Http\Controller;
use Api\User\Requests\CreateUserRequest;
use Api\User\Requests\SignupRequest;
use Api\User\Requests\UserGroupsRequest;
use Api\User\Services\UserService;
use Api\User\Services\TeamService;

/**
 * @OA\Schema()
 *
@OA\Schema(schema="UserCreateSchema", allOf={
    @OA\Schema(ref="#/components/schemas/User"),
    @OA\Schema(@OA\Property( property="reseller_reference_code", type="string", description="Reseller reference code`TODO properly save on user creation`" )),
    @OA\Schema(@OA\Property(
        property="contacts",
        type="array",
        @OA\Items(
            allOf={
                @OA\Schema(ref="#/components/schemas/Contact"),
            }
        ),
    )),
    @OA\Schema(
        @OA\Property(
            property="extensions",
            type="array",
            @OA\Items(
                allOf={
                    @OA\Schema(ref="#/components/schemas/Extension"),
                    @OA\Schema(ref="#/components/schemas/Voicemail"),
                }
            ),
        ),
    ),
}),
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

    public function __construct(UserService $userService, TeamService $teamService)
    {
        $this->userService = $userService;
        $this->teamService = $teamService;
    }

    public function getAll()
    {
        $resourceOptions = $this->parseResourceOptions();

        $data = $this->userService->getAll($resourceOptions);
        $parsedData = $this->parseData($data, $resourceOptions, 'users');

        return $this->response($parsedData);
    }

    public function getById(string $userId)
    {
        $resourceOptions = $this->parseResourceOptions();

        $data = $this->userService->getById($userId, $resourceOptions);
        $parsedData = $this->parseData($data, $resourceOptions, null);

        return $this->response($parsedData);
    }

    public function getMe()
    {
        $resourceOptions = $this->parseResourceOptions();

        $data = $this->userService->getMe($resourceOptions);
        $parsedData = $this->parseData($data, $resourceOptions, null);

        return $this->response($parsedData);
    }


    public function create(CreateUserRequest $request)
    {
        $data = $request->get('user', []);

        return $this->response($this->userService->create($data), 201);
    }

    public function update($hash, Request $request)
    {
        $data = $request->get('user', []);

        return $this->response($this->userService->update($userId, $data));
    }

    public function activate(string $hash)
    {
        $response = $this->response($this->userService->activate($hash));
        // dd($response);
        return $response;
    }

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
    @OA\Post(
        tags={"Domain", "User"},
        path="/user/signup",
        summary="User signup",
        description="Signup a user to domain. A user can have several extensions, several contacts and a bunch of settings.",
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
            description="Domain created response",
            @OA\JsonContent(
                allOf={
                    @OA\Schema(ref="#/components/schemas/DomainCreateSchema"),
                },
                examples={
                    "Create domain basic example1": {
                        "summary": "Create domain with language settings",
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

    /**
     * User signup
     *
     * @param SignupRequest $request
     * @return void
     */
    public function signup(SignupRequest $request)
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
}
