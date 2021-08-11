<?php
namespace Gruz\FPBX\OASchemas;

    /**
    @OA\Parameter(
        name="extension_uuid",
        in="path",
        description="Extension UUID",
        required=true,
        @OA\Schema(
            type="string",
            format="uuid",
            example="541f8e60-5ae0-11eb-bb80-b31e63f668c8",
        )
    ),

    @OA\Schema(schema="ExtensionCreateSchema", allOf={
        @OA\Schema(@OA\Property( property="reseller_reference_code", type="string", description="Reseller reference code`TODO properly save on user creation`" )),
        @OA\Schema(ref="#/components/schemas/Extension"),
    }),

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
    }),

    */