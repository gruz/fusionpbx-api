<?php

namespace Gruz\FPBX\Http\Controllers;

use Gruz\FPBX\Requests\DomainSignupRequest;
use Gruz\FPBX\Requests\DomainActivateRequest;
use Gruz\FPBX\Services\PostponedActionService;

/**
 * @OA\Schema()
 */
class DomainController extends AbstractBrunoController
{
    /**
     * Create a domain
     *
     * # General notes
     *
     * When creating a domain you must provide at least one admin user (`is_admin` option in `users->user` object ).
     *
     * All admins will be emailed to confirm domain creation.
     *
     * # Domain settings
     *
     * You can override default domain settings
     *
     * See [Default Settings](https://docs.fusionpbx.com/en/latest/advanced/default_settings.html#default-settings)
     * and [Override a Default Setting for one domain](https://docs.fusionpbx.com/en/latest/advanced/domains.html#override-a-default-setting-for-one-domain)
     * to understand how override works.
     *
     *
    @OA\Post(
        tags={"Domain"},
        path="/domain/signup",
        x={"route-$path"="fpbx.domain.signup"},
        @OA\RequestBody(
            description="Domain information",
            required=true,
            @OA\MediaType(
                mediaType="application/json",
                @OA\Schema(
                    allOf={
                        @OA\Schema(ref="#/components/schemas/DomainCreateSchema")
                    },
                ),
            ),
        ),
    )
     */
    public function signup(DomainSignupRequest $request, PostponedActionService $postponedActionService)
    {
        return $this->response($postponedActionService->createMany($request->all()), 201);
    }

    /**
     * Activate domain by code.
     *
    @OA\Get(
        tags={"Domain"},
        path="/domain/activate/{code}",
        @OA\Parameter(
            name="code",
            in="path",
            required=true,
            @OA\Schema(
                type="string",
                format="integer",
                example="537349",
            )
        ),
        @OA\Response(
            description="Application name and version",
            response=200,
            @OA\MediaType(
                mediaType="application/json",
                @OA\Examples(example="200", summary="Success", value={"domain_name":"mertz22.com","domain_description":"Created via Factory during tests","domain_enabled":true,"domain_uuid":"cd801673-f879-4ac6-8693-25e73d0721a1","message":"Team created. Login using username <b>marisa36@watsica.net<\/b>, domain name <b>mertz22.com<\/b> and password <b>!uiS:9:<\/b>"}),
            )
        ),
        @OA\Response(
            description="Bad data",
            response=422,
            @OA\MediaType(
                mediaType="application/json",
                @OA\Examples(example="Already activated", summary="", value={"errors":{{"status":"422","code":422,"title":"Validation error","detail":"Domain already activated"}}}),
                @OA\Examples(example="No activation code found", summary="", value={"errors":{{"status":"422","code":422,"title":"Validation error","detail":"The selected code is invalid."}}}),
                @OA\Examples(example="Expired", summary="", value={"errors":{{"status":"422","code":422,"title":"Validation error","detail":"Domain activation link expired"}}}),
            )
        ),

    )
     */
    public function activate($code, DomainActivateRequest $request, PostponedActionService $postponedActionService)
    {
        return $this->response($postponedActionService->executeByHash($code), 201);
    }

    /**
     * Resend domain signup verification link
     *
    @ OA\Get(
        tags={"Domain"},
        path="/domain/resend/{hash}",
        @ OA\Parameter(
            name="hash",
            in="path",
            required=true,
            @ OA\Schema(
                type="string",
                format="uuid",
                example="541f8e60-5ae0-11eb-bb80-b31e63f668c8",
            )
        ),
        @ OA\Response(response=200, description="`TODO Stub` Success ..."),
        @ OA\Response(response=400, description="`TODO Stub` Could not ..."),
    )
     */
    // public function resend($hash, PostponedActionExecuteRequest $request, PostponedActionService $postponedActionService)
    // {
    //     return $this->response($postponedActionService->executeByHash($hash), 201);
    // }

    /**
     * Update a domain `TODO Implement`
     *
     * Depending on permissions will allow or not updating certain values
     *
    @ OA\Put(
        tags={"Domain"},
        path="/domain/{domain_uuid}",
        @ OA\Parameter(ref="#/components/parameters/domain_uuid"),
        @ OA\RequestBody(
            description="Domain information",
            required=true,
            @ OA\JsonContent(
                allOf={
                    @ OA\Schema(ref="#/components/schemas/DomainSchema"),
                },
                example={
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
        @ OA\Response(
            response=200,
            description="Domain created response",
            @ OA\JsonContent(
                allOf={
                    @ OA\Schema(ref="#/components/schemas/Domain"),
                },
            ),
        ),
        @ OA\Response(
            response=400,
            description="`TODO Stub` Could not created domain",
            @ OA\JsonContent(
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
    @ OA\Delete(
        tags={"Domain"},
        path="/domain/{domian_uuid}",
        @ OA\Parameter(ref="#/components/parameters/domain_uuid"),
        @ OA\Response(response=200, description="`TODO Stub` Success ..."),
        @ OA\Response(response=400, description="`TODO Stub` Could not ..."),
    )
     */
}
