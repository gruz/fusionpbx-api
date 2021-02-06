<?php

namespace Api\Domain\Controllers;

use OpenApi\Annotations as OA;
use Api\User\Services\TeamService;
use Api\User\Services\UserService;
use Infrastructure\Http\Controller;
use Api\Domain\Requests\DomainSignupRequest;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use InvalidArgumentException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Api\User\Exceptions\WrongSignupDataException;
use Exception;

class DomainController extends Controller
{
    /**
     * @var TeamService
     */
    private $teamService;

    public function __construct(UserService $userService, TeamService $teamService)
    {
        $this->userService = $userService;
        $this->teamService = $teamService;
        new OA\JsonContent([]);
    }


    /**
       @OA\Post(
        tags={"Domain", "User"},
        path="/domain/signup1",
        summary="Create a domain",
            @OA\Response(
                response="default",
                description="登录",
                @OA\JsonContent(
                    ref="#/components/schemas/Domain",
                    examples={
                        "200": {
                            "summary": "登录成功",
                            "value": {
                                "code": 200,
                                "message": "登录成功",
                                "data": {
                                    "user_token": "fwefnn2342423",
                                    "role_type": "admin" 
                                }
                            }
                        },
                        "403": {
                            "summary": "登录失败",
                            "value": {
                                "code": 403,
                                "message": "登录失败",
                                "data": null
                            }
                        },
                    }
                ),
            )
        )
    */

    /**
    @OA\Post(
        tags={"Domain", "User"},
        path="/domain/signup",
        summary="Create a domain",
        description="
# Domain Settings and User Settings

You can pass here domain and user settings overriding default ones setup for FPBX.

See [Default Settings](https://docs.fusionpbx.com/en/latest/advanced/default_settings.html#default-settings) to see
available settings
and [Override a Default Setting for one domain](https://docs.fusionpbx.com/en/latest/advanced/domains.html#override-a-default-setting-for-one-domain)
to understand how override works.

So to override a setting (e.g. set another UI language), your domain setting object should look like:
```json
{}
```
        ",
        @OA\RequestBody(
            description="Domain information",
            required=true,
            @OA\JsonContent(
                allOf={
                    @OA\Schema(
                        ref="#/components/schemas/Domain",
                        x={"hidden"={
                            "aa", "aa"
                        }},
                    ),
                    @OA\Schema(
                        @OA\Property(
                            property="settings",
                            type="array",
                            @OA\Items(
                                @OA\Schema(ref="#/components/schemas/Domain_setting"),
                            ),
                        ),
                    ),
                    @OA\Schema(
                        @OA\Property(
                            property="users",
                            type="array",
                            @OA\Items(
                                allOf={
                                    @OA\Schema(ref="#/components/schemas/User"),
                                    @OA\Schema(ref="#/components/schemas/User_setting"),
                                }
                            ),
                        ),
                    ),
                },
                examples={
                    "Create domain": {},
                    "Create domain with language settings": {
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
            response=200,
            description="Domain created response",
            @OA\JsonContent(
                allOf={
                    @OA\Schema(ref="#/components/schemas/Domain"),
                    @OA\Schema(ref="#/components/schemas/Domain_setting"),
                    @OA\Schema(
                        @OA\Property(
                            property="users",
                            type="array",
                            @OA\Items(
                                allOf={
                                    @OA\Schema(ref="#/components/schemas/User"),
                                    @OA\Schema(ref="#/components/schemas/User_setting"),
                                }
                            ),
                        ),
                    ),
                },
            ),
        ),
        @OA\Response(
            response=400,
            description="`TODO` Could not created domain",
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
     * Domain signup
     *
     * @param DomainSignupRequest $request
     * @return Optimus\Bruno\Illuminate\Http\JsonResponse
     *
     * @throws BadRequestException
     * @throws InvalidArgumentException
     * @throws BindingResolutionException
     * @throws WrongSignupDataException
     * @throws Exception
     */
    public function signup(DomainSignupRequest $request)
    {
        $data = $request->get('team', []);

        $data['isTeam'] = true;
        $data['user_enabled'] = 'true';
        $data['group_name'] = env('MOTHERSHIP_DOMAIN_DEFAULT_GROUP_NAME');

        return $this->response($this->teamService->create($data), 201);
    }
}
