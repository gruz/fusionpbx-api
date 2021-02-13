<?php
namespace Api\User;

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

    @OA\Schema(
        schema="user_includes",
        type="array",
        @OA\Items(type="string",
            enum = { "groups", "status", "domain", "permissions", "emails","extensions", },
        )
    ),

    @OA\Schema(schema="UserCreateSchema", allOf={
        @OA\Schema(@OA\Property( property="reseller_reference_code", type="string", description="Reseller reference code`TODO properly save on user creation`" )),
        @OA\Schema(ref="#/components/schemas/UserWithRelatedItemsSchema"),
    }),

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
                        @OA\Schema(ref="#/components/schemas/Extension"),
                        @OA\Schema(ref="#/components/schemas/Voicemail"),
                    }
                ),
            ),
        ),
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