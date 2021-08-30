<?php

namespace Gruz\FPBX\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Gruz\FPBX\Requests\GetUserRequest;
use Gruz\FPBX\Requests\UserLoginRequest;
use Gruz\FPBX\Services\Fpbx\UserService;
use Gruz\FPBX\Requests\CreateUserRequest;
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
                @OA\Examples(example="User signup with extension", summary="", value={ "domain_name": "mertz12.com", "user_email": "alyson5.dietrich@howe.com", "password": ".Apantera1", "username": "alyson5.dietrich@howe.com", "reseller_reference_code": "Code01", "extensions": { { "extension": 1721, "password": ".Apantera1", "voicemail_password": "9563", "effective_caller_id_name": "William Reichel", "effective_caller_id_number": 141 } } }),
                @OA\Examples(example="User signup with extension and contact", summary="", value={ "domain_name": "mertz12.com", "user_email": "alyson5.dietrich@howe.com", "password": ".Apantera1", "username": "alyson5.dietrich@howe.com", "reseller_reference_code": "Code01", "extensions": { { "extension": 1721, "password": ".Apantera1", "voicemail_password": "9563", "effective_caller_id_name": "William Reichel", "effective_caller_id_number": 141 } } }),
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
            description="User already exists",
            response=422,
            @OA\MediaType(
                mediaType="application/json",
                @OA\Examples(example="User already exists", summary="", value={"errors":{{"status":"422","code":422,"title":"Validation error","detail":"The user email has already been taken."},{"status":"422","code":422,"title":"Validation error","detail":"The username has already been taken."},{"status":"422","code":422,"title":"Validation error","detail":"The extensions.0.extension has already been taken."}}}),
                @OA\Examples(example="Bad data provided - validation fails", summary="", value={"errors":{{"status":"422","code":422,"title":"Validation error","detail":"Password invalid. Min 6 symbols, case sensitive, at least one lowercase, one uppercase and one digit"},{"status":"422","code":422,"title":"Validation error","detail":"The extensions.0.password format is invalid."},{"status":"422","code":422,"title":"Validation error","detail":"Voicemail password must be between 4 and 10 digits"}}}),
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
        path="/user/activate/{user_uuid}/{user_enabled}",
        x={"route-$path"="fpbx.user.activate"},
        @OA\Parameter(ref="#/components/parameters/user_uuid"),
        @OA\Parameter(
            name="user_enabled",
            in="path",
            description="User activation code from verification email",
            required=true,
            @OA\Schema(
                type="integer",
                example="567134",
            )
        ),
        @OA\Response(
            description="Application name and version",
            response=200,
            @OA\MediaType(
                mediaType="application/json",
                @OA\Examples(example="200", summary="Success", value={"message":"User activated","user":{"user_uuid":"541f8e60-5ae0-11eb-bb80-b31e63f668c8","domain_uuid":"cd801673-f879-4ac6-8693-25e73d0721a1","username":"alyson.dietrich2@howe.com","contact_uuid":null,"api_key":null,"user_enabled":"true","add_user":"admin","add_date":"2021-08-30 14:08:36.342260+0000"}}),
            )
        ),
        @OA\Response(
            description="User already exists",
            response=422,
            @OA\MediaType(
                mediaType="application/json",
                @OA\Examples(example="User already enabled", summary="", value={"errors":{{"status":"422","code":422,"title":"Validation error","detail":"User already enabled"}}}),
                @OA\Examples(example="Bad or expired validation code", summary="", value={"errors":{{"status":"422","code":422,"title":"Validation error","detail":"Invalid or expired validation code"}}}),
            )
        ),
    )
     */
    public function activate(string $user_uuid, string $user_enabled, UserActivateRequest $request, UserService $userService)
    {
        $response = $this->response($userService->activate($user_uuid, $user_enabled));

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
        },
        @OA\Parameter(ref="#/components/parameters/user_includes[]"),
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
    @OA\Get(
        tags={"User"},
        path="/users",
        security={{"bearer_auth": {}}},
        @OA\Parameter(ref="#/components/parameters/user_includes[]"),
        @OA\Parameter(ref="#/components/parameters/limit"),
        @OA\Parameter(ref="#/components/parameters/page"),
        @OA\Response(
            response=200,
            description="Get users list in the current domain",
            @OA\MediaType(
                mediaType="application/json",
                @OA\Examples(example="/users?limit=2", summary="Get 2 users /users?limit=2", value={"users":{{"user_uuid":"d9eee3d2-73b6-41d2-aa8e-87aad28f3dbf","domain_uuid":"294036b0-2715-4877-b5f3-6833afbf71b5","username":"marisa36@watsica.net","contact_uuid":null,"api_key":null,"user_enabled":"true","add_user":"admin","add_date":"2021-08-23 17:13:40.449035+0000","account_code":null},{"user_uuid":"859e345e-b77e-4b90-968c-4b8749753319","domain_uuid":"0438502e-7dec-4db0-b82b-9ce7f1d3a66e","username":"admin","contact_uuid":null,"api_key":null,"user_enabled":"true","add_user":null,"add_date":null,"account_code":null}}}),
                @OA\Examples(example="/users?includes[]=extensions&includes[]=extensions.voicemail&limit=2", summary="Get two users with extensions and voicemail /users?includes[]=extensions&includes[]=extensions.voicemail&limit=2", value={"users":{{"user_uuid":"d9eee3d2-73b6-41d2-aa8e-87aad28f3dbf","domain_uuid":"294036b0-2715-4877-b5f3-6833afbf71b5","username":"marisa36@watsica.net","contact_uuid":null,"api_key":null,"user_enabled":"true","add_user":"admin","add_date":"2021-08-23 17:13:40.449035+0000","account_code":null,"extensions":{{"extension_uuid":"9be803a0-5b6e-4f88-973e-a55df27e0d09","domain_uuid":"294036b0-2715-4877-b5f3-6833afbf71b5","extension":"172","accountcode":"account-4676","effective_caller_id_name":"Dario Hammes","effective_caller_id_number":"157","outbound_caller_id_name":"Abraham Heller","outbound_caller_id_number":"169","emergency_caller_id_name":"Mireya Will","emergency_caller_id_number":"114","directory_first_name":"Alvena Lemke","directory_last_name":"Will","directory_visible":"false","directory_exten_visible":"true","max_registrations":null,"limit_max":"2","limit_destination":"error\/user_busy","missed_call_app":"string","missed_call_data":"string","user_context":"mertz09.com","toll_allow":"local","call_timeout":"30","call_group":"sales","call_screen_enabled":"true","user_record":"all","hold_music":null,"auth_acl":"string","cidr":"string","sip_force_contact":"NDLB-connectile-dysfunction","nibble_account":null,"sip_force_expires":null,"mwi_account":"herminio94@keebler.com","sip_bypass_media":"bypass-media-after-bridge","unique_id":null,"dial_string":"\/location\/of\/the\/endpoint","dial_user":null,"dial_domain":null,"do_not_disturb":null,"forward_all_destination":null,"forward_all_enabled":null,"forward_busy_destination":null,"forward_busy_enabled":null,"forward_no_answer_destination":null,"forward_no_answer_enabled":null,"forward_user_not_registered_destination":null,"forward_user_not_registered_enabled":null,"follow_me_uuid":null,"follow_me_enabled":"string","follow_me_destinations":"string","enabled":"true","description":"Extension created while testing API","absolute_codec_string":"absolute\/codec\/string","force_ping":"true","pivot":{"user_uuid":"d9eee3d2-73b6-41d2-aa8e-87aad28f3dbf","extension_uuid":"9be803a0-5b6e-4f88-973e-a55df27e0d09"},"voicemail":{"domain_uuid":"294036b0-2715-4877-b5f3-6833afbf71b5","voicemail_uuid":"11b82fe5-bc28-4400-a225-438dae065c4a","voicemail_id":"172","greeting_id":"0","voicemail_alternate_greet_id":"0","voicemail_mail_to":"shemar.steuber@runolfsdottir.com","voicemail_sms_to":"+1 (906) 407-0690","voicemail_transcription_enabled":"true","voicemail_attach_file":"\/path\/to\/file","voicemail_file":null,"voicemail_local_after_email":"false","voicemail_enabled":"false","voicemail_description":"Deserunt distinctio iusto omnis qui debitis. Enim itaque est omnis illo id debitis. Saepe sit eligendi corporis et. Exercitationem quis ullam illum sed.","voicemail_name_base64":null,"voicemail_tutorial":"string"}},{"extension_uuid":"87f2745e-4956-4f6a-aea8-4e0c1e34013e","domain_uuid":"294036b0-2715-4877-b5f3-6833afbf71b5","extension":"123","accountcode":"account-3558","effective_caller_id_name":"Mateo Abernathy","effective_caller_id_number":"174","outbound_caller_id_name":"Dr. Haylee Bartoletti Jr.","outbound_caller_id_number":"195","emergency_caller_id_name":"Virginie Waelchi","emergency_caller_id_number":"200","directory_first_name":"Romaine Mayer Sr.","directory_last_name":"Lowe","directory_visible":"true","directory_exten_visible":"false","max_registrations":null,"limit_max":"7","limit_destination":"error\/user_busy","missed_call_app":"string","missed_call_data":"string","user_context":"mertz09.com","toll_allow":"local","call_timeout":"30","call_group":"sales","call_screen_enabled":"false","user_record":"outbound","hold_music":"local_stream:\/\/default","auth_acl":"string","cidr":"string","sip_force_contact":"NDLB-tls-connectile-dysfunction","nibble_account":null,"sip_force_expires":null,"mwi_account":"mpfeffer@metz.com","sip_bypass_media":"proxy-media","unique_id":null,"dial_string":"\/location\/of\/the\/endpoint","dial_user":null,"dial_domain":null,"do_not_disturb":null,"forward_all_destination":null,"forward_all_enabled":null,"forward_busy_destination":null,"forward_busy_enabled":null,"forward_no_answer_destination":null,"forward_no_answer_enabled":null,"forward_user_not_registered_destination":null,"forward_user_not_registered_enabled":null,"follow_me_uuid":null,"follow_me_enabled":"string","follow_me_destinations":"string","enabled":"true","description":"Extension created while testing API","absolute_codec_string":"absolute\/codec\/string","force_ping":null,"pivot":{"user_uuid":"d9eee3d2-73b6-41d2-aa8e-87aad28f3dbf","extension_uuid":"87f2745e-4956-4f6a-aea8-4e0c1e34013e"},"voicemail":{"domain_uuid":"294036b0-2715-4877-b5f3-6833afbf71b5","voicemail_uuid":"63267702-3e90-4851-a933-048ca15abdcc","voicemail_id":"123","greeting_id":"0","voicemail_alternate_greet_id":"0","voicemail_mail_to":"virginia.sauer@yahoo.com","voicemail_sms_to":"1-475-877-2613","voicemail_transcription_enabled":"false","voicemail_attach_file":"\/path\/to\/file","voicemail_file":null,"voicemail_local_after_email":"false","voicemail_enabled":"false","voicemail_description":"Iusto sint ex est delectus ut. Qui facere cum omnis ducimus nisi. Ab omnis itaque vitae perspiciatis. Omnis repellat delectus et at dolor expedita voluptas.","voicemail_name_base64":null,"voicemail_tutorial":"string"}}}},{"user_uuid":"859e345e-b77e-4b90-968c-4b8749753319","domain_uuid":"0438502e-7dec-4db0-b82b-9ce7f1d3a66e","username":"admin","contact_uuid":null,"api_key":null,"user_enabled":"true","add_user":null,"add_date":null,"account_code":null,"extensions":{}}}}),
            )
        ),
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
    @OA\Get(
        tags={"User"},
        path="/user/{user_uuid}",
        security={{"bearer_auth": {}}},
        @OA\Parameter(ref="#/components/parameters/user_uuid"),
        @OA\Parameter(ref="#/components/parameters/user_includes[]"),
        @OA\Response(
            response=200,
            description="Get users",
            @OA\MediaType(
                mediaType="application/json",
                @OA\Examples(example="/user/{user_uuid}", summary="Get users basic info /user/{user_uuid}", value={"user_uuid":"78fbfbed-9cfd-4c10-9c8e-912a3e06ec89","domain_uuid":"f53332bd-a81f-476f-ab22-e3826a07599f","username":"alyson.dietrich2@howe.com","contact_uuid":null,"api_key":null,"user_enabled":"15bc0fc2-1652-4e05-aca6-328937099361","add_user":"admin","add_date":"2021-08-23 20:23:52.800667+0000","account_code":null}),
                @OA\Examples(example="/user/{user_uuid}?includes[]=extensions",
                    summary="Get users with data /user/{user_uuid}?includes[]=extensions",
                    value={"user_uuid":"78fbfbed-9cfd-4c10-9c8e-912a3e06ec89","domain_uuid":"f53332bd-a81f-476f-ab22-e3826a07599f","username":"alyson.dietrich2@howe.com","contact_uuid":null,"api_key":null,"user_enabled":"15bc0fc2-1652-4e05-aca6-328937099361","add_user":"admin","add_date":"2021-08-23 20:23:52.800667+0000","account_code":null,"extensions":{{"extension_uuid":"4b5710f0-a2c1-4d5d-8cfa-a7aa63688f94","domain_uuid":"f53332bd-a81f-476f-ab22-e3826a07599f","extension":"169","accountcode":"account-8364","effective_caller_id_name":"William Reichel","effective_caller_id_number":"141","outbound_caller_id_name":"Lelia Wolff","outbound_caller_id_number":"120","emergency_caller_id_name":"Dr. Darrel Tillman","emergency_caller_id_number":"180","directory_first_name":"Mr. Carmine Becker","directory_last_name":"Pollich","directory_visible":"false","directory_exten_visible":"true","max_registrations":null,"limit_max":"4","limit_destination":"error\/user_busy","missed_call_app":"string","missed_call_data":"string","user_context":"mertz14.com","toll_allow":"international","call_timeout":"30","call_group":"billing","call_screen_enabled":"true","user_record":"all","hold_music":null,"auth_acl":"string","cidr":"string","sip_force_contact":"NDLB-tls-connectile-dysfunction","nibble_account":null,"sip_force_expires":null,"mwi_account":"lwitting@yahoo.com","sip_bypass_media":"bypass-media","unique_id":null,"dial_string":"\/location\/of\/the\/endpoint","dial_user":null,"dial_domain":null,"do_not_disturb":null,"forward_all_destination":null,"forward_all_enabled":null,"forward_busy_destination":null,"forward_busy_enabled":null,"forward_no_answer_destination":null,"forward_no_answer_enabled":null,"forward_user_not_registered_destination":null,"forward_user_not_registered_enabled":null,"follow_me_uuid":null,"follow_me_enabled":"string","follow_me_destinations":"string","enabled":"false","description":"Extension created while testing API","absolute_codec_string":"absolute\/codec\/string","force_ping":"false","pivot":{"user_uuid":"78fbfbed-9cfd-4c10-9c8e-912a3e06ec89","extension_uuid":"4b5710f0-a2c1-4d5d-8cfa-a7aa63688f94"}},{"extension_uuid":"2223ef11-d69c-4388-8e80-1a632db39ebe","domain_uuid":"f53332bd-a81f-476f-ab22-e3826a07599f","extension":"156","accountcode":"account-4644","effective_caller_id_name":"Mr. Albin Shields","effective_caller_id_number":"149","outbound_caller_id_name":"Oda Muller I","outbound_caller_id_number":"112","emergency_caller_id_name":"Felipa Kilback","emergency_caller_id_number":"108","directory_first_name":"Lonie Olson","directory_last_name":"Yost","directory_visible":"true","directory_exten_visible":"true","max_registrations":null,"limit_max":"5","limit_destination":"error\/user_busy","missed_call_app":"string","missed_call_data":"string","user_context":"mertz14.com","toll_allow":"domestic","call_timeout":"30","call_group":"billing","call_screen_enabled":"true","user_record":"all","hold_music":null,"auth_acl":"string","cidr":"string","sip_force_contact":"NDLB-connectile-dysfunction","nibble_account":null,"sip_force_expires":null,"mwi_account":"xondricka@hammes.org","sip_bypass_media":"bypass-media","unique_id":null,"dial_string":"\/location\/of\/the\/endpoint","dial_user":null,"dial_domain":null,"do_not_disturb":null,"forward_all_destination":null,"forward_all_enabled":null,"forward_busy_destination":null,"forward_busy_enabled":null,"forward_no_answer_destination":null,"forward_no_answer_enabled":null,"forward_user_not_registered_destination":null,"forward_user_not_registered_enabled":null,"follow_me_uuid":null,"follow_me_enabled":"string","follow_me_destinations":"string","enabled":"false","description":"Extension created while testing API","absolute_codec_string":"absolute\/codec\/string","force_ping":"true","pivot":{"user_uuid":"78fbfbed-9cfd-4c10-9c8e-912a3e06ec89","extension_uuid":"2223ef11-d69c-4388-8e80-1a632db39ebe"}}}}),
            )
        ),
        @OA\Response(
            response=422,
            description="Bad UUID",
            @OA\MediaType(
                mediaType="application/json",
                @OA\Examples(example="Bad UUID", summary="Bad UUID", value={"errors":{{"status":"422","code":422,"title":"Validation error","detail":"The user uuid must be a valid UUID."}}}),
            )
        ),
        @OA\Response(
            response=404,
            description="No user found",
            @OA\MediaType(
                mediaType="application/json",
                @OA\Examples(example="No user found", summary="No user found", value={"status":"error","code":404,"message":"User not found"}),
            )
        ),
    )
     */
    public function getById(string $user_uuid, GetUserRequest $request)
    {
        $resourceOptions = $this->parseResourceOptions();

        $data = $this->userService->getById($user_uuid, $resourceOptions);

        $parsedData = $this->parseData($data, $resourceOptions, null);

        return $this->response($parsedData);
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

    /**
     *
     * User logout
     *
     * Login user and return access token
     *
    @OA\Post(
        tags={"User"},
        path="/user/logout",
        security={{"bearer_auth": {}}},
        @OA\Response(
            response=200,
            description="Login successfull",
            @OA\JsonContent(
                example={"message":"Tokens Revoked"}
            ),
        ),
    )
     */
    public function logout()
    {
        auth()->user()->tokens()->delete();

        return [
            'message' => __('Tokens Revoked')
        ];
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
            description="Password resent link sent",
            @OA\MediaType(
                mediaType="application/json",
                @OA\Examples(example="Password resent link sent", summary="Password resent link sent", value={"message":"Check your email"}),
            )
        ),
        @OA\Response(
            response=403,
            description="User disabled",
            @OA\MediaType(
                mediaType="application/json",
                @OA\Examples(example="User in domain exists, but disabled", summary="User in domain exists, but disabled", value={"status":"error","code":403,"message":"User disabled"}),
            )
        ),
        @OA\Response(
            response=422,
            description="User created response",
            @OA\MediaType(
                mediaType="application/json",
                @OA\Examples(example="No domain found", summary="No domain found", value={"errors":{{"status":"422","code":422,"title":"Validation error","detail":"The selected domain name is invalid."}}}),
                @OA\Examples(example="No email found", summary="No email found", value={"errors":{{"status":"422","code":422,"title":"Validation error","detail":"User not found"}}}),
                @OA\Examples(example="No domain and email found", summary="No domain and email found", value={"errors":{{"status":"422","code":422,"title":"Validation error","detail":"The selected domain name is invalid."},{"status":"422","code":422,"title":"Validation error","detail":"The selected user email is invalid."}}}),
            )
        ),
    )
     */
    public function forgotPassword(UserForgotPasswordRequestApi $request, UserPasswordService $userPasswordService)
    {
        $data = $request->only('user_email', 'domain_name');

        return $this->response($userPasswordService->generateResetToken($data));
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
}
