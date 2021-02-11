<?php

namespace Api\Domain\Controllers;

use OpenApi\Annotations as OA;
use Api\User\Services\TeamService;
use Api\User\Services\UserService;
use Infrastructure\Http\Controller;
use Api\Domain\Requests\DomainSignupRequest;

/**
 * @OA\Schema()
 */
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
    }

    /**
     * Create a domain
     *
     *`TODO Finish description`
     * # Domain Settings and User Settings
     *
     * When creating a domain you must provide at least one
     *
     * You can pass here domain and user settings overriding default ones setup for FPBX.
     *
     * See [Default Settings](https://docs.fusionpbx.com/en/latest/advanced/default_settings.html#default-settings) to see
     * available settings
     * and [Override a Default Setting for one domain](https://docs.fusionpbx.com/en/latest/advanced/domains.html#override-a-default-setting-for-one-domain)
     * to understand how override works.
     *
     * So to override a setting (e.g. set another UI language), your domain setting object should look like:
     * ```json
     * {}
     * ```
    @OA\Post(
        tags={"Domain"},
        path="/domain/signup",
        @OA\RequestBody(
            description="Domain information",
            required=true,
            @OA\JsonContent(
                allOf={
                    @OA\Schema(ref="#/components/schemas/DomainCreateSchema"),
                },
                examples={
                    "Create domain all fields": {},
                    "Create domain basic example": {
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
    public function signup(DomainSignupRequest $request)
    {
        $data = $request->get('team', []);

        $data['isTeam'] = true;
        $data['user_enabled'] = 'true';
        $data['group_name'] = env('MOTHERSHIP_DOMAIN_DEFAULT_GROUP_NAME');

        return $this->response($this->teamService->create($data), 201);
    }

    /**
     * Update a domain `TODO Implement`
     *
     * Depending on permissions will allow or not updating certain values
     *
    @OA\Put(
        tags={"Domain"},
        path="/domain/{domain_uuid}",
        @OA\Parameter(ref="#/components/parameters/domain_uuid"),
        @OA\RequestBody(
            description="Domain information",
            required=true,
            @OA\JsonContent(
                allOf={
                    @OA\Schema(ref="#/components/schemas/DomainSchema"),
                },
                examples={
                    "Domain all fields": {},
                    "Create domain basic example": {
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
                    @OA\Schema(ref="#/components/schemas/Domain"),
                },
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
     * Delete a domain `TODO descendant delete with users, extensions etc`
     *
     * Not implemented yet
     *
    @OA\Delete(
        tags={"Domain"},
        path="/domain/{domian_uuid}",
        @OA\Parameter(ref="#/components/parameters/domain_uuid"),
        @OA\Response(response=200, description="`TODO Stub` Success ..."),
        @OA\Response(response=400, description="`TODO Stub` Could not ..."),
    )
    */
}
