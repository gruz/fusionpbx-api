<?php

namespace Gruz\FPBX\Http\Controllers;

use Illuminate\Http\Request;
use Gruz\FPBX\Requests\UserLoginRequest;
use Gruz\FPBX\Services\Fpbx\UserService;
use Gruz\FPBX\Requests\CreateUserRequest;
use Illuminate\Support\Facades\Hash;
use Gruz\FPBX\Requests\UserActivateRequest;
use Gruz\FPBX\Services\UserPasswordService;
use Gruz\FPBX\Requests\UserSignupRequestApi;
use Gruz\FPBX\Requests\UserForgotPasswordRequestApi;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * @OA\Schema()
 */
class UserController extends AbstractBrunoController
{
    /**
     * @var UserService
     */
    private $userService;

    public function __construct(
        UserService $userService
    ) {
        $this->userService = $userService;
    }


    /**
     *
     * User signup
     *
     * Signup a user to domain. A user can have several extensions, several contacts and a bunch of settings.
     *
    @OA\Post(
        tags={"User"},
        path="/user/signup",
        x={"route-$path"="fpbx.user.signup"},
        @OA\RequestBody(
            description="User information",
            required=true,
            @OA\MediaType(
                mediaType="application/json",
                @OA\Schema(ref="#/components/schemas/UserCreateSchema"),
                @OA\Examples(example=200, summary="", value={"username":"alyson3.dietrich@howe.com","add_user":"admin","domain_uuid":"8cffb9b5-41a4-4dfe-9ae5-619a4394634f","add_date":"2021-08-25 07:21:12.125369+0000","user_enabled":"f6b78951340bd4813ea5a5a275e08d1220ad51c7","user_uuid":"a935de0c-539d-4443-8036-a8120aedda01","domain":{"domain_uuid":"8cffb9b5-41a4-4dfe-9ae5-619a4394634f","domain_parent_uuid":null,"domain_name":"mertz12.com","domain_enabled":true,"domain_description":"Created via Factory during tests"}}),
                @OA\Examples(example=300, summary="", value={"name":1}),
                @OA\Examples(example=400, summary="", value={"name":1})
            )
        ),
        @OA\Response(
            response=200,
            description="User created response",
            @OA\MediaType(
                mediaType="application/json",
                @OA\Examples(example="200", summary="Success", value={"username":"alyson3.dietrich@howe.com","add_user":"admin","domain_uuid":"8cffb9b5-41a4-4dfe-9ae5-619a4394634f","add_date":"2021-08-25 07:21:12.125369+0000","user_enabled":"f6b78951340bd4813ea5a5a275e08d1220ad51c7","user_uuid":"a935de0c-539d-4443-8036-a8120aedda01","domain":{"domain_uuid":"8cffb9b5-41a4-4dfe-9ae5-619a4394634f","domain_parent_uuid":null,"domain_name":"mertz12.com","domain_enabled":true,"domain_description":"Created via Factory during tests"}}),
            )
        ),

        @OA\Response(
            description="Application name and version",
            response=422,
            @OA\MediaType(
                mediaType="application/json",
                @OA\Examples(example="200", summary="User already exists", value={"errors":{{"status":"422","code":422,"title":"Validation error","detail":"The user email has already been taken."},{"status":"422","code":422,"title":"Validation error","detail":"The username has already been taken."},{"status":"422","code":422,"title":"Validation error","detail":"The extensions.0.extension has already been taken."}}}),
            )
        ),
    )
     */
    public function signup(UserSignupRequestApi $request)
    {
        $data = $request->except('domain_uuid');

        return $this->response($this->userService->create($data), 201);
    }

    /**
     * Activate user by email link. In cases it's and admin user, activate domain as well
     *
    @OA\Get(
        tags={"User"},
        path="/verify-email/{id}/{hash}",
        x={"route-$path"="verification.verify"},
        @OA\Parameter(
            name="id",
            in="path",
            description="User uuid passed to the verification email",
            required=true,
            @OA\Schema(
                type="string",
                format="uuid",
                example="973add20-16b8-467d-ad02-42ffd1cc4aa4",
            )
        ),
        @OA\Parameter(
            name="hash",
            in="path",
            description="User activation hash from verification emails",
            required=true,
            @OA\Schema(
                type="string",
                example="65db8e98584c1d9a83b1b64371d157049f470d75",
            )
        ),
        @OA\Response(
            description="Application name and version",
            response=200,
            @OA\MediaType(
                mediaType="application/json",
                @OA\Examples(example="200", summary="Success", value={"message":"User activated","user":{"user_uuid":"973add20-16b8-467d-ad02-42ffd1cc4aa4","domain_uuid":"8c292d13-0e70-4a08-8f50-eb8bb4348ae4","username":"marisa36@watsica.net","contact_uuid":null,"api_key":null,"user_enabled":"true","add_user":"admin","add_date":"2021-08-23 17:15:21.262935+0000","account_code":null}}),
            )
        ),
    )
     */
    public function activate(string $id, string $hash, UserActivateRequest $request, UserService $userService)
    {
        $response = $this->response($userService->activate($hash));

        return $response;
    }


    /**
     * Gets currently logged in user info
     *
    @OA\Get(
        tags={"User"},
        path="/user",
        security={{"bearer_auth": {}}},
        x={
            "route-$path"="fpbx.user.own",
            "route-$middlewares"="api,auth:sanctum"
        },
        @OA\Parameter(
            description="Relations to be attached",
            allowReserved=true,
            name="includes[]",
            in="query",
            @OA\Schema(ref="#/components/schemas/user_includes")
        ),
        @OA\Response(
            response=200,
            description="Get current user data, possible with relations",
            @OA\MediaType(
                mediaType="application/json",
                @OA\Examples(example="Only user info", summary="Only user info", value={"user_uuid":"8bd760bd-0146-4c4f-ae41-916d3aa2ac90","domain_uuid":"3b89f95e-1576-4f0a-9f7b-40f572ff6eee","username":"alyson.dietrich2@howe.com","contact_uuid":null,"api_key":null,"user_enabled":"true","add_user":"admin","add_date":"2021-08-24 10:20:20.112414+0000","account_code":null}),
                @OA\Examples(example="includes[]=extensions", summary="includes[]=extensions", value={"user_uuid":"8bd760bd-0146-4c4f-ae41-916d3aa2ac90","domain_uuid":"3b89f95e-1576-4f0a-9f7b-40f572ff6eee","username":"alyson.dietrich2@howe.com","contact_uuid":null,"api_key":null,"user_enabled":"true","add_user":"admin","add_date":"2021-08-24 10:20:20.112414+0000","account_code":null,"extensions":{{"extension_uuid":"fb308456-d33e-42d7-bd1f-0fac86be2563","domain_uuid":"3b89f95e-1576-4f0a-9f7b-40f572ff6eee","extension":"169","accountcode":"account-8364","effective_caller_id_name":"William Reichel","effective_caller_id_number":"141","outbound_caller_id_name":"Lelia Wolff","outbound_caller_id_number":"120","emergency_caller_id_name":"Dr. Darrel Tillman","emergency_caller_id_number":"180","directory_first_name":"Mr. Carmine Becker","directory_last_name":"Pollich","directory_visible":"false","directory_exten_visible":"true","max_registrations":null,"limit_max":"4","limit_destination":"error\/user_busy","missed_call_app":"string","missed_call_data":"string","user_context":"mertz18.com","toll_allow":"international","call_timeout":"30","call_group":"billing","call_screen_enabled":"true","user_record":"all","hold_music":null,"auth_acl":"string","cidr":"string","sip_force_contact":"NDLB-tls-connectile-dysfunction","nibble_account":null,"sip_force_expires":null,"mwi_account":"lwitting@yahoo.com","sip_bypass_media":"bypass-media","unique_id":null,"dial_string":"\/location\/of\/the\/endpoint","dial_user":null,"dial_domain":null,"do_not_disturb":null,"forward_all_destination":null,"forward_all_enabled":null,"forward_busy_destination":null,"forward_busy_enabled":null,"forward_no_answer_destination":null,"forward_no_answer_enabled":null,"forward_user_not_registered_destination":null,"forward_user_not_registered_enabled":null,"follow_me_uuid":null,"follow_me_enabled":"string","follow_me_destinations":"string","enabled":"true","description":"Extension created while testing API","absolute_codec_string":"absolute\/codec\/string","force_ping":"false","pivot":{"user_uuid":"8bd760bd-0146-4c4f-ae41-916d3aa2ac90","extension_uuid":"fb308456-d33e-42d7-bd1f-0fac86be2563"}},{"extension_uuid":"972f20b2-b582-482d-aaff-832cfa1db323","domain_uuid":"3b89f95e-1576-4f0a-9f7b-40f572ff6eee","extension":"156","accountcode":"account-4644","effective_caller_id_name":"Mr. Albin Shields","effective_caller_id_number":"149","outbound_caller_id_name":"Oda Muller I","outbound_caller_id_number":"112","emergency_caller_id_name":"Felipa Kilback","emergency_caller_id_number":"108","directory_first_name":"Lonie Olson","directory_last_name":"Yost","directory_visible":"true","directory_exten_visible":"true","max_registrations":null,"limit_max":"5","limit_destination":"error\/user_busy","missed_call_app":"string","missed_call_data":"string","user_context":"mertz18.com","toll_allow":"domestic","call_timeout":"30","call_group":"billing","call_screen_enabled":"true","user_record":"all","hold_music":null,"auth_acl":"string","cidr":"string","sip_force_contact":"NDLB-connectile-dysfunction","nibble_account":null,"sip_force_expires":null,"mwi_account":"xondricka@hammes.org","sip_bypass_media":"bypass-media","unique_id":null,"dial_string":"\/location\/of\/the\/endpoint","dial_user":null,"dial_domain":null,"do_not_disturb":null,"forward_all_destination":null,"forward_all_enabled":null,"forward_busy_destination":null,"forward_busy_enabled":null,"forward_no_answer_destination":null,"forward_no_answer_enabled":null,"forward_user_not_registered_destination":null,"forward_user_not_registered_enabled":null,"follow_me_uuid":null,"follow_me_enabled":"string","follow_me_destinations":"string","enabled":"true","description":"Extension created while testing API","absolute_codec_string":"absolute\/codec\/string","force_ping":"true","pivot":{"user_uuid":"8bd760bd-0146-4c4f-ae41-916d3aa2ac90","extension_uuid":"972f20b2-b582-482d-aaff-832cfa1db323"}}}}),
                @OA\Examples(example="includes[]=contacts", summary="includes[]=contacts", value={"user_uuid":"8bd760bd-0146-4c4f-ae41-916d3aa2ac90","domain_uuid":"3b89f95e-1576-4f0a-9f7b-40f572ff6eee","username":"alyson.dietrich2@howe.com","contact_uuid":null,"api_key":null,"user_enabled":"true","add_user":"admin","add_date":"2021-08-24 10:20:20.112414+0000","account_code":null,"contacts":{{"contact_uuid":"c0302a33-f3eb-4c2a-9587-72846cfe7202","domain_uuid":"3b89f95e-1576-4f0a-9f7b-40f572ff6eee","contact_parent_uuid":null,"contact_type":"customer","contact_organization":"Littel Inc","contact_name_prefix":"Prof.","contact_name_given":"Emilio","contact_name_middle":"A.","contact_name_family":"Frami","contact_name_suffix":"Jr.","contact_nickname":"muller.donnie","contact_title":"Ms.","contact_role":"Online Marketing Analyst","contact_category":"Contacts added via API","contact_url":"http:\/\/www.hermann.info\/molestias-minus-voluptas-harum-reiciendis","contact_time_zone":"America\/Los_Angeles","contact_note":"Quo veritatis magnam hic rerum culpa facilis sint explicabo. Voluptas et aut magni adipisci nulla et. Exercitationem optio reprehenderit voluptatem dolore excepturi et.","last_mod_date":null,"last_mod_user":null,"pivot":{"user_uuid":"8bd760bd-0146-4c4f-ae41-916d3aa2ac90","contact_uuid":"c0302a33-f3eb-4c2a-9587-72846cfe7202"}},{"contact_uuid":"b118e705-8e19-4a06-b998-3fff11fdca6e","domain_uuid":"3b89f95e-1576-4f0a-9f7b-40f572ff6eee","contact_parent_uuid":null,"contact_type":"supplier","contact_organization":"Kub PLC","contact_name_prefix":"Prof.","contact_name_given":"Patricia","contact_name_middle":"A.","contact_name_family":"Mohr","contact_name_suffix":"DVM","contact_nickname":"kautzer.cordie","contact_title":"Prof.","contact_role":"Administrative Services Manager","contact_category":"Contacts added via API","contact_url":"http:\/\/www.rogahn.com\/est-voluptate-ut-velit-aut-non-ut.html","contact_time_zone":"Africa\/Kinshasa","contact_note":"Quis placeat labore hic reiciendis omnis enim corporis. Fugit beatae repellendus amet commodi id odit sequi inventore. Praesentium est et harum et voluptatem. Sint maxime deserunt laboriosam harum.","last_mod_date":null,"last_mod_user":null,"pivot":{"user_uuid":"8bd760bd-0146-4c4f-ae41-916d3aa2ac90","contact_uuid":"b118e705-8e19-4a06-b998-3fff11fdca6e"}}}}),
                @OA\Examples(example="includes[]=contacts&includes[]=extensions", summary="includes[]=contacts&includes[]=extensions", value={"user_uuid":"8bd760bd-0146-4c4f-ae41-916d3aa2ac90","domain_uuid":"3b89f95e-1576-4f0a-9f7b-40f572ff6eee","username":"alyson.dietrich2@howe.com","contact_uuid":null,"api_key":null,"user_enabled":"true","add_user":"admin","add_date":"2021-08-24 10:20:20.112414+0000","account_code":null,"contacts":{{"contact_uuid":"c0302a33-f3eb-4c2a-9587-72846cfe7202","domain_uuid":"3b89f95e-1576-4f0a-9f7b-40f572ff6eee","contact_parent_uuid":null,"contact_type":"customer","contact_organization":"Littel Inc","contact_name_prefix":"Prof.","contact_name_given":"Emilio","contact_name_middle":"A.","contact_name_family":"Frami","contact_name_suffix":"Jr.","contact_nickname":"muller.donnie","contact_title":"Ms.","contact_role":"Online Marketing Analyst","contact_category":"Contacts added via API","contact_url":"http:\/\/www.hermann.info\/molestias-minus-voluptas-harum-reiciendis","contact_time_zone":"America\/Los_Angeles","contact_note":"Quo veritatis magnam hic rerum culpa facilis sint explicabo. Voluptas et aut magni adipisci nulla et. Exercitationem optio reprehenderit voluptatem dolore excepturi et.","last_mod_date":null,"last_mod_user":null,"pivot":{"user_uuid":"8bd760bd-0146-4c4f-ae41-916d3aa2ac90","contact_uuid":"c0302a33-f3eb-4c2a-9587-72846cfe7202"}},{"contact_uuid":"b118e705-8e19-4a06-b998-3fff11fdca6e","domain_uuid":"3b89f95e-1576-4f0a-9f7b-40f572ff6eee","contact_parent_uuid":null,"contact_type":"supplier","contact_organization":"Kub PLC","contact_name_prefix":"Prof.","contact_name_given":"Patricia","contact_name_middle":"A.","contact_name_family":"Mohr","contact_name_suffix":"DVM","contact_nickname":"kautzer.cordie","contact_title":"Prof.","contact_role":"Administrative Services Manager","contact_category":"Contacts added via API","contact_url":"http:\/\/www.rogahn.com\/est-voluptate-ut-velit-aut-non-ut.html","contact_time_zone":"Africa\/Kinshasa","contact_note":"Quis placeat labore hic reiciendis omnis enim corporis. Fugit beatae repellendus amet commodi id odit sequi inventore. Praesentium est et harum et voluptatem. Sint maxime deserunt laboriosam harum.","last_mod_date":null,"last_mod_user":null,"pivot":{"user_uuid":"8bd760bd-0146-4c4f-ae41-916d3aa2ac90","contact_uuid":"b118e705-8e19-4a06-b998-3fff11fdca6e"}}},"extensions":{{"extension_uuid":"fb308456-d33e-42d7-bd1f-0fac86be2563","domain_uuid":"3b89f95e-1576-4f0a-9f7b-40f572ff6eee","extension":"169","accountcode":"account-8364","effective_caller_id_name":"William Reichel","effective_caller_id_number":"141","outbound_caller_id_name":"Lelia Wolff","outbound_caller_id_number":"120","emergency_caller_id_name":"Dr. Darrel Tillman","emergency_caller_id_number":"180","directory_first_name":"Mr. Carmine Becker","directory_last_name":"Pollich","directory_visible":"false","directory_exten_visible":"true","max_registrations":null,"limit_max":"4","limit_destination":"error\/user_busy","missed_call_app":"string","missed_call_data":"string","user_context":"mertz18.com","toll_allow":"international","call_timeout":"30","call_group":"billing","call_screen_enabled":"true","user_record":"all","hold_music":null,"auth_acl":"string","cidr":"string","sip_force_contact":"NDLB-tls-connectile-dysfunction","nibble_account":null,"sip_force_expires":null,"mwi_account":"lwitting@yahoo.com","sip_bypass_media":"bypass-media","unique_id":null,"dial_string":"\/location\/of\/the\/endpoint","dial_user":null,"dial_domain":null,"do_not_disturb":null,"forward_all_destination":null,"forward_all_enabled":null,"forward_busy_destination":null,"forward_busy_enabled":null,"forward_no_answer_destination":null,"forward_no_answer_enabled":null,"forward_user_not_registered_destination":null,"forward_user_not_registered_enabled":null,"follow_me_uuid":null,"follow_me_enabled":"string","follow_me_destinations":"string","enabled":"true","description":"Extension created while testing API","absolute_codec_string":"absolute\/codec\/string","force_ping":"false","pivot":{"user_uuid":"8bd760bd-0146-4c4f-ae41-916d3aa2ac90","extension_uuid":"fb308456-d33e-42d7-bd1f-0fac86be2563"}},{"extension_uuid":"972f20b2-b582-482d-aaff-832cfa1db323","domain_uuid":"3b89f95e-1576-4f0a-9f7b-40f572ff6eee","extension":"156","accountcode":"account-4644","effective_caller_id_name":"Mr. Albin Shields","effective_caller_id_number":"149","outbound_caller_id_name":"Oda Muller I","outbound_caller_id_number":"112","emergency_caller_id_name":"Felipa Kilback","emergency_caller_id_number":"108","directory_first_name":"Lonie Olson","directory_last_name":"Yost","directory_visible":"true","directory_exten_visible":"true","max_registrations":null,"limit_max":"5","limit_destination":"error\/user_busy","missed_call_app":"string","missed_call_data":"string","user_context":"mertz18.com","toll_allow":"domestic","call_timeout":"30","call_group":"billing","call_screen_enabled":"true","user_record":"all","hold_music":null,"auth_acl":"string","cidr":"string","sip_force_contact":"NDLB-connectile-dysfunction","nibble_account":null,"sip_force_expires":null,"mwi_account":"xondricka@hammes.org","sip_bypass_media":"bypass-media","unique_id":null,"dial_string":"\/location\/of\/the\/endpoint","dial_user":null,"dial_domain":null,"do_not_disturb":null,"forward_all_destination":null,"forward_all_enabled":null,"forward_busy_destination":null,"forward_busy_enabled":null,"forward_no_answer_destination":null,"forward_no_answer_enabled":null,"forward_user_not_registered_destination":null,"forward_user_not_registered_enabled":null,"follow_me_uuid":null,"follow_me_enabled":"string","follow_me_destinations":"string","enabled":"true","description":"Extension created while testing API","absolute_codec_string":"absolute\/codec\/string","force_ping":"true","pivot":{"user_uuid":"8bd760bd-0146-4c4f-ae41-916d3aa2ac90","extension_uuid":"972f20b2-b582-482d-aaff-832cfa1db323"}}}}),
            )
        ),
        @OA\Response(
            response=401,
            description="Unauthenticated",
            @OA\MediaType(
                mediaType="application/json",
                @OA\Examples(example="Unauthenticated", summary="Unauthenticated", value={"errors":{{"status":401,"code":0,"message":"Unauthenticated."}}}),
            )
        ),
    )
     */
    public function getMe()
    {
        $resourceOptions = $this->parseResourceOptions();

        $data = $this->userService->getMe($resourceOptions);
        $parsedData = $this->parseData($data, $resourceOptions, null);

        return $this->response($parsedData);
    }



    /**
     * Get user list in domain
     *
     * `TODO`, describe in docs and return only some fields available for other users,
     * add parameters in query to select contact info, extension
     *
    @ OA\Get(
        tags={"User"},
        path="/users",
        @ OA\Parameter(
            description="Relations to be attached",
            allowReserved=true,
            name="includes[]",
            in="query",
            @ OA\Schema(
                type="array",
                @ OA\Items(type="string",
                    enum = { "groups", "status", "domain", "permissions", "emails","extensions", },
                )
            )
        ),
        @ OA\Response(
            response=200,
            description="`TODO Stub` Could not created domain",
            @ OA\JsonContent(type="array",
                @ OA\Items(ref="#/components/schemas/UserWithRelatedItemsSchema"),
            ),
        ),
        security={{"bearer_auth": {}}}
    )
     */
    public function getAll()
    {
        $resourceOptions = $this->parseResourceOptions();

        $data = $this->userService->getAll($resourceOptions);
        $parsedData = $this->parseData($data, $resourceOptions, 'users');

        return $this->response($parsedData);
    }

    /**
     * Get user info by ID
     *
    @ OA\Get(
        tags={"User"},
        path="/user/{user_uuid}",
        @ OA\Parameter(ref="#/components/parameters/user_uuid"),
        @ OA\Parameter(
            description="Relations to be attached",
            allowReserved=true,
            name="includes[]",
            in="query",
            @ OA\Schema(ref="#/components/schemas/user_includes")
        ),
        @ OA\Response(response=200, description="`TODO Stub` Success ..."),
        @ OA\Response(response=400, description="`TODO Stub` Could not ..."),
    )
     */
    public function getById(string $userId)
    {
        $resourceOptions = $this->parseResourceOptions();

        $data = $this->userService->getById($userId, $resourceOptions);
        $parsedData = $this->parseData($data, $resourceOptions, null);

        return $this->response($parsedData);
    }

    /**
     * Creates a user inside a domain by a user with permissions to create. It's not a signup!
     *
    @ OA\Post(
        tags={"User"},
        path="/user",
        @ OA\RequestBody(
            description="User information",
            required=true,
            @ OA\JsonContent(
                ref="#/components/schemas/UserCreateSchema",
                example={
                    "Create a user": {},
                    "Create a user basic example": {
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
            description="`TODO Stub` Could not created domain",
            @ OA\JsonContent(ref="#/components/schemas/User"),
        ),
        @ OA\Response(response=400, description="`TODO Stub` Could not ..."),
    )
     */
    public function create(CreateUserRequest $request)
    {
        $data = $request->get('user', []);

        return $this->response($this->userService->create($data), 201);
    }


    /**
     * Update oneself or another user if having enough permissions
     *
    @ OA\Put(
        tags={"User"},
        path="/user/{user_uuid}",
        @ OA\Parameter(ref="#/components/parameters/user_uuid"),
        @ OA\RequestBody(
            description="User information",
            required=true,
            @ OA\JsonContent(
                ref="#/components/schemas/UserCreateSchema",
                example={
                    "Create a user": {},
                    "Create a user basic example": {
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
        @ OA\Response(response=200, description="`TODO Stub` Success ..."),
        @ OA\Response(response=400, description="`TODO Stub` Could not ..."),
    )
     */
    public function update($userId, Request $request)
    {
        $data = $request->get('user', []);

        return $this->response($this->userService->update($userId, $data));
    }



    /**
     * Delete a domain `TODO descendant delete user with extenions, contacts, handle last domain admin delition`
     *
     * Not implemented yet
     *
    @ OA\Delete(
        tags={"User"},
        path="/user/{user_uuid}",
        @ OA\Parameter(ref="#/components/parameters/user_uuid"),
        @ OA\Response(response=200, description="`TODO Stub` Success ..."),
        @ OA\Response(response=400, description="`TODO Stub` Could not ..."),
    )
     */
    public function delete($userId)
    {
        return $this->response($this->userService->delete($userId));
    }

    /**
     * User forgot password - request email link to reset password
     *
    @OA\Post(
        tags={"User"},
        path="/user/forgot-password",
        x={"route-$path"="fpbx.user.forgot-password"},
        @OA\RequestBody(
            description="User information to reset his password",
            required=true,
            @OA\JsonContent(
                ref="#/components/schemas/UserForgotPasswordSchema",
                example={
                    "Request email with link basic example": {
                        "summary": "Request email with link basic example",
                        "value": {
                            "user_email":"your_user@email.com",
                            "domain_name":"jimmie.biz"
                        }
                    },
                }
            )
        ),
        @OA\Response(
            response=200,
            description="Password resent link response",
            @OA\JsonContent(
                ref="#/components/schemas/UserCreateSchema",
                example={
                    "Password resent link basic example": {
                        "username": "user_Destany.Windler",
                        "domain_uuid": "142ce990-6e16-11eb-8ad7-99f61fb0e7c6"
                    },
                }
            ),
        ),
        @OA\Response(
            response=422,
            description="Validation error - empty email",
            @OA\JsonContent(
                type="object",
                @OA\Property(
                    property="errors",
                    type="array",
                    example={{
                        "status": "422",
                        "code": 0,
                        "title": "Validation error",
                        "detail": "The user email is required."
                    }},
                    @OA\Items(
                        @OA\Property(
                          property="status",
                          type="string",
                          example="422"
                       ),
                       @OA\Property(
                          property="code",
                          type="number",
                          example=0
                       ),
                       @OA\Property(
                          property="title",
                          type="string",
                          example="Validation error"
                       ),
                       @OA\Property(
                          property="detail",
                          type="string",
                          example="The user email is required."
                       ),
                    ),
                )
            ),
        ),
    )
     */
    public function forgotPassword(UserForgotPasswordRequestApi $request, UserPasswordService $userPasswordService)
    {
        $data = $request->only('user_email', 'domain_name');

        return $this->response($userPasswordService->generateResetToken($data));
    }

    /**
     *
     * User login
     *
     * Login user and return access token
     *
    @OA\Post(
        tags={"User"},
        path="/user/login",
        @OA\RequestBody(
            description="User information",
            required=true,
            @OA\JsonContent(
                example={
                    "domain_name" : "192.168.0.160",
                    "username" : "admin",
                    "password" : "admin"
                }
            ),
        ),
        @OA\Response(
            response=200,
            description="Login successfull",
            @OA\JsonContent(
                example={
                    "access_token": "18|o7yAzJLTcRFECUUrBERn44ITisQTGDDJUY1KHdJ0"
                }
            ),
        ),
        @OA\Response(
            response=422,
            description="Bad domain",
            @OA\JsonContent(
                example={
                    "errors": {
                        {
                            "status": "422",
                            "code": 422,
                            "title": "Validation error",
                            "detail": "The selected domain name is invalid."
                        }
                    }
                }
            ),
        ),
        @OA\Response(
            response=401,
            description="Invalid credentials.",
            @OA\JsonContent(
                example={
                    "status": "error",
                    "code": 401,
                    "message": "Invalid credentials.",
                }
            ),
        ),
    )
     */
    public function login(UserLoginRequest $request, UserService $userService)
    {
        $user = $userService->getUserByUsernameAndDomain($request->get('username'), $request->get('domain_name'));

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw new UnauthorizedHttpException('Basic', __('Invalid credentials.'), null, 401);
            // throw ValidationException::withMessages([
            //     'username' => ['The provided credentials are incorrect.'],
            // ]);
        }

        $token = $user->createToken($request->username)->plainTextToken;

        return $this->response(['access_token' => $token]);
    }
}
