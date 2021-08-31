<?php
namespace Gruz\FPBX\OASchemas;

    /**
    @OA\Parameter(
        name="extension_includes[]",
        description="Relations to be attached",
        allowReserved=true,
        in="query",
        @OA\Schema(ref="#/components/schemas/extension_includes")
    ),

    @OA\Schema(
        schema="extension_includes",
        type="array",
        @OA\Items(type="string",
            enum = {
                "voicemail",
                "users",
                "users.contacts",
                "users.groups",
                "users.status",
                "users.domain",
                "users.permissions",
                "users.emails",
                "users.extensions",
                "users.pushtokens"
            },
        )
    ),

    @OA\Schema(schema="ExtensionCreateSchema",
        allOf={
            @OA\Schema(ref="#/components/schemas/Extension"),
        },
    ),

    @OA\Schema(schema="ExtensionWithRelatedUsersSchema", allOf={
        @OA\Schema(ref="#/components/schemas/Extension"),
        @OA\Schema(@OA\Property(
            property="users",
            type="array",
            @OA\Items(
                allOf={
                    @OA\Schema(ref="#/components/schemas/User"),
                }
            ),
        )),
        @OA\Schema(@OA\Property(
            property="voicemail",
            ref="#/components/schemas/Voicemail",
        )),
    }),

    */
