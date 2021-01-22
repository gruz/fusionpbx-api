<?php

namespace Api\User\Controllers;

use Api\User\Services\TeamService;
use Api\User\Services\UserService;
use Infrastructure\Http\Controller;
use Api\User\Requests\SignupDomainRequest;

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
     * @OA\Post(
     *   path="/signup/domain",
     *     summary="Create a domain",
     *     description="Creates a domain. Depending on the configuration the domain `domain_name` 
     *                      can be a subdomain of the main domain or an independent domain",
     *     @OA\RequestBody(
     *         description="Client side search object",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Domain"),
     *     ),
     *     @OA\Response(
     *      response=200,
     *      description="Get application name and version",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(
     *               @OA\Property(property="title", type="string", description="APP_NAME variable"),
     *               @OA\Property(property="version", type="string", description="Fit tag version")
     *           ),
     *           @OA\Examples(summary="App info", value={"title": "FusionPBX API", "version" : "0.0.8-62-g192d97b"}),
     *      )
     *     )
     * )
     */
    /**
     * User signup
     *
     * @param SignupDomainRequest $request
     * @return void
     */
    public function signup(SignupDomainRequest $request)
    {
        $data = $request->get('team', []);

        $data['isTeam'] = true;
        $data['user_enabled'] = 'true';
        $data['group_name'] = env('MOTHERSHIP_DOMAIN_DEFAULT_GROUP_NAME');

        return $this->response($this->teamService->create($data), 201);
    }
}
