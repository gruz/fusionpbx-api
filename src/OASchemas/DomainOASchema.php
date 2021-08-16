<?php

namespace Gruz\FPBX\OASchemas;

/**
    @OA\Parameter(
        name="domain_uuid",
        in="path",
        description="Domain UUID",
        required=true,
        @OA\Schema(
            type="string",
            format="uuid",
            example="541f8e60-5ae0-11eb-bb80-b31e63f668c8",
        )
    )

    @OA\Schema(
        schema="DomainSchema",
        allOf={
            @OA\Schema(ref="#/components/schemas/Domain"),
            @OA\Schema(
                @OA\Property(
                    property="settings",
                    type="array",
                    @OA\Items(
                        allOf={
                            @OA\Schema(ref="#/components/schemas/DomainSetting"),
                        }
                    ),
                ),
            ),
        }
    )

    @OA\Schema(
        schema="DomainCreateSchema",
        allOf={
            @OA\Schema(@OA\Property(
                property="is_subdomain",
                type="boolean",
                default="false",
                description="Determines is the current domain should be a top level domain or a subdomain
                        of the mothership domain. Mothership domain should be equal to default FusionPBX domain
                        and should be set in .env file like `MOTHERSHIP_DOMAIN=demo.sip.lan`"
            )),
            @OA\Schema(ref="#/components/schemas/DomainSchema"),
            @OA\Schema(
                @OA\Property(
                    property="users",
                    type="array",
                    @OA\Items(
                        allOf={
                            @OA\Schema(@OA\Property(
                                property="is_admin",
                                type="boolean",
                                default="false",
                                description="At least one user must be an admin (`is_admin=true`) when creating a new domain"
                            )),
                            @OA\Schema(ref="#/components/schemas/UserCreateSchema"),
                        }
                    ),
                ),
            ),
        },
        required={
            "is_subdomain",
            "domain_name",
            "users"
        }
    )
 */
