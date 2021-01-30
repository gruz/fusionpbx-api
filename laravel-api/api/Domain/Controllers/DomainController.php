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
        x={"query-args-$ref"="#/components/schemas/Domain"},
        path="/signup/domain",
        summary="Create a domain",
        description="Creates a domain. Depending on the configuration the domain `domain_name` can be a subdomain of the main domain or an independent domain",
        @OA\RequestBody(
            description="Client side search object",
            required=true,
            @OA\JsonContent(
                allOf={
                    @OA\Schema(ref="#/components/schemas/Domain"),
                    @OA\Schema(ref="#/components/schemas/User"),
                    @OA\Schema(
                        @OA\Property(property="iidd", type="integer"),
                        @OA\Property(property="created_at", ref="#/components/schemas/Domain/properties/domain_uuid")
                        )
                    },
                    x={
                        "model-input-fields"={
                            "#/components/schemas/Domain",
                            "#/components/schemas/User",
                        },
                    },
            ),
        ),
        @OA\Response(
        response=200,
        description="Get application name and version",
        @OA\JsonContent(
                allOf={
                    @OA\Schema(ref="#/components/schemas/Domain"),
                    @OA\Schema(
                        @OA\Property(property="iidd", type="integer"),
                        @OA\Property(property="created_at", ref="#/components/schemas/Domain/properties/domain_uuid")
                    )
                }
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
