<?php

namespace Gruz\FPBX\OASchemas;

/**
    @OA\Parameter(
        name="uuid",
        in="path",
        description="Entity UUID",
        required=true,
        @OA\Schema(
            type="string",
            format="uuid",
            example="541f8e60-5ae0-11eb-bb80-b31e63f668c8",
        )
    ),
    @OA\Parameter(
        name="limit",
        in="query",
        description="Limit",
        @OA\Schema(
            type="integer"
        )
    ),
    @OA\Parameter(
        name="page",
        in="query",
        description="Page",
        @OA\Schema(
            type="integer"
        )
    ),

    @OA\Response(
        response="Unauthenticated",
        description="Unauthenticated",
        @OA\MediaType(
            mediaType="application/json",
            @OA\Examples(example="Unauthenticated", summary="", value={"status":"error","code":401,"message":"Unauthenticated."}),
        )
    ),
    @OA\Response(
        response="UnverifiedResponse",
        description="Email is not verified",
        @OA\MediaType(
            mediaType="application/json",
            @OA\Examples(example="Email is not verified", summary="", value={"status":"error","code":403,"message":"Your email address is not verified."}),
        )
    ),

    @OA\Response(
        response="BadUuidResponse",
        description="Bad UUID. The `:entity:` place will be replaced with `user`, `extension` etc.",
        @OA\MediaType(
            mediaType="application/json",
            @OA\Examples(example="Bad UUID", summary="Bad UUID", value={"errors":{{"status":"422","code":422,"title":"Validation error","detail":"The :entity: uuid must be a valid UUID."}}}),
        )
    ),

    @OA\Response(
        response="EntityNotFoundResponse",
        description="Not found The `:Entity:` place will be replaced with `User`, `Extension` etc.",
        @OA\MediaType(
            mediaType="application/json",
            @OA\Examples(example="Not found", summary="", value={"status":"error","code":404,"message":":Entity: not found"}),
        )
    ),

 */
