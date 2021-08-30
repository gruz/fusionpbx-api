<?php

namespace Gruz\FPBX\OASchemas;

/**
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

 */
