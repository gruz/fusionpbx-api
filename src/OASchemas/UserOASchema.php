<?php

namespace Gruz\FPBX\OASchemas;

/**
    @OA\Parameter(
        name="user_uuid",
        in="path",
        description="User UUID",
        required=true,
        @OA\Schema(
            type="string",
            format="uuid",
            example="541f8e60-5ae0-11eb-bb80-b31e63f668c8",
        )
    ),

    @OA\Parameter(
        name="user_includes[]",
        description="Relations to be attached",
        allowReserved=true,
        in="query",
        @OA\Schema(ref="#/components/schemas/user_includes")
    ),

    @OA\Schema(
        schema="user_includes",
        type="array",
        @OA\Items(type="string",
            enum = { "groups", "status", "domain", "permissions", "emails", "extensions", "extension.voicemail", "pushtokens" },
        )
    ),

    @OA\Schema(schema="UserCreateSchema",
        allOf={
            @OA\Schema(@OA\Property(
                property="reseller_reference_code",
                type="string",
                description="It's a non-FusionPBX option developed for a specific case.
                    In most cases you need to ignore this.
                    You may use it if you need to know allow users registration only if a reference code is provided.
                    Check package configuration file (`config/fusionpbx-api.php`) for further reference.
                    If it's still not clear, then ask the developer for further explanations."
            )),
            @OA\Schema(ref="#/components/schemas/UserWithRelatedItemsSchema"),
        },
        required={"username", "password", "user_email"}
    ),

    @OA\Schema(schema="UserWithRelatedItemsSchema", allOf={
        @OA\Schema(ref="#/components/schemas/User"),
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
                        @OA\Schema(
                            ref="#/components/schemas/Extension",
                        ),
                        @OA\Schema(ref="#/components/schemas/Voicemail"),
                    },
                    required={"extension", "password", "voicemail_password"}
                ),
            ),
            required={"extensions"}
        ),
    }),

    @OA\Schema(schema="ResendActivation", allOf={
        @OA\Schema(@OA\Property(
            property="domain_name",
            type="string",
            description="Domain name"
        )),
        @OA\Schema(@OA\Property(
            property="user_email",
            type="email",
            description="User email"
        )),
    }),

    @OA\Schema(schema="UserUpdatePasswordSchema", allOf={
        @OA\Schema(@OA\Property(
            property="token",
            type="string",
            description="Unique token to enable user to reset password"
        )),
        @OA\Schema(@OA\Property(
            property="password",
            type="string",
            description="New user password to set"
        )),
        @OA\Schema(@OA\Property(
            property="password_confiramtion",
            type="string",
            description="Confirmation of new user password to set"
        )),
        @OA\Schema(@OA\Property(
            property="user_email",
            type="string",
            description="User email"
        )),
    }),

    @OA\Schema(schema="UserForgotPasswordSchema", allOf={
        @OA\Schema(@OA\Property(
            property="user_email",
            type="string",
            description="User email"
        )),
        @OA\Schema(@OA\Property(
            property="domain_name",
            type="string",
            description="Name of a domain to which user belongs"
        )),
    }),

    */
