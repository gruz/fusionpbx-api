<?php

namespace Gruz\FPBX\Http\Controllers;

use Gruz\FPBX\Requests\GetUuidRequest;
use Gruz\FPBX\Services\Fpbx\ExtensionService;
use Gruz\FPBX\Requests\CreateExtensionRequest;
use Gruz\FPBX\Requests\UpdateExtensionRequest;

/**
 * @OA\Schema()
 */
class ExtensionController extends AbstractBrunoController
{
    private $extensionService;

    public function __construct(ExtensionService $extensionService)
    {
        $this->extensionService = $extensionService;
    }

    /**
     * Get all extensions in domain
     *
     *
    @OA\Get(
        tags={"Extension"},
        path="/extensions",
        x={"route-$action"="getAll"},
        security={{"bearer_auth": {}}},
        @OA\Parameter(ref="#/components/parameters/extension_includes[]"),
        @OA\Parameter(ref="#/components/parameters/limit"),
        @OA\Parameter(ref="#/components/parameters/page"),
        @OA\Response(
            response=200,
            description="Get users list in the current domain",
            @OA\MediaType(
                @OA\Schema(ref="#/components/schemas/ExtensionWithRelatedUsersSchema"),
                mediaType="application/json",
                @OA\Examples(example="/users?limit=2&page=1", summary="", value={"extensions":{{"extension_uuid":"dc2cd102-65be-4779-8a2f-d5769362724e","domain_uuid":"9b6787d9-a0c6-484f-8e39-3f64f457db81","extension":"171","accountcode":"account-9937","effective_caller_id_name":"Amanda Goldner","effective_caller_id_number":"145","outbound_caller_id_name":"Maximilian Heathcote","outbound_caller_id_number":"148","emergency_caller_id_name":"Norris Collier","emergency_caller_id_number":"128","directory_first_name":"Arianna Zieme","directory_last_name":"Hahn","directory_visible":"true","directory_exten_visible":"true","max_registrations":null,"limit_max":"2","limit_destination":"error\/user_busy","missed_call_app":"string","missed_call_data":"string","user_context":"mertz27.com","toll_allow":"international","call_timeout":"30","call_group":"billing","call_screen_enabled":"true","user_record":null,"hold_music":"local_stream:\/\/default","auth_acl":"string","cidr":"string","sip_force_contact":null,"nibble_account":null,"sip_force_expires":null,"mwi_account":"patrick.kerluke@gmail.com","sip_bypass_media":"bypass-media-after-bridge","unique_id":null,"dial_string":"\/location\/of\/the\/endpoint","dial_user":null,"dial_domain":null,"do_not_disturb":null,"forward_all_destination":null,"forward_all_enabled":null,"forward_busy_destination":null,"forward_busy_enabled":null,"forward_no_answer_destination":null,"forward_no_answer_enabled":null,"forward_user_not_registered_destination":null,"forward_user_not_registered_enabled":null,"follow_me_uuid":null,"follow_me_enabled":"string","follow_me_destinations":"string","enabled":"true","description":"Extension created while testing API","absolute_codec_string":"absolute\/codec\/string","force_ping":"false"},{"extension_uuid":"47d8b854-ba17-49ba-8f52-a2c2d2649a90","domain_uuid":"9b6787d9-a0c6-484f-8e39-3f64f457db81","extension":"164","accountcode":"account-6963","effective_caller_id_name":"Joelle Rodriguez","effective_caller_id_number":"144","outbound_caller_id_name":"Otis Shields","outbound_caller_id_number":"144","emergency_caller_id_name":"Ahmad Muller","emergency_caller_id_number":"172","directory_first_name":"D'angelo Dach Sr.","directory_last_name":"Walker","directory_visible":"true","directory_exten_visible":"false","max_registrations":null,"limit_max":"6","limit_destination":"error\/user_busy","missed_call_app":"string","missed_call_data":"string","user_context":"mertz27.com","toll_allow":"international","call_timeout":"30","call_group":"billing","call_screen_enabled":"false","user_record":"local","hold_music":"local_stream:\/\/default","auth_acl":"string","cidr":"string","sip_force_contact":"NDLB-connectile-dysfunction-2.0","nibble_account":null,"sip_force_expires":null,"mwi_account":"ferne.torphy@yahoo.com","sip_bypass_media":null,"unique_id":null,"dial_string":"\/location\/of\/the\/endpoint","dial_user":null,"dial_domain":null,"do_not_disturb":null,"forward_all_destination":null,"forward_all_enabled":null,"forward_busy_destination":null,"forward_busy_enabled":null,"forward_no_answer_destination":null,"forward_no_answer_enabled":null,"forward_user_not_registered_destination":null,"forward_user_not_registered_enabled":null,"follow_me_uuid":null,"follow_me_enabled":"string","follow_me_destinations":"string","enabled":"true","description":"Extension created while testing API","absolute_codec_string":"absolute\/codec\/string","force_ping":"true"}}}),
                @OA\Examples(example="/users?limit=2&page=1&includes[]=users", summary="", value={"extensions":{{"extension_uuid":"dc2cd102-65be-4779-8a2f-d5769362724e","domain_uuid":"9b6787d9-a0c6-484f-8e39-3f64f457db81","extension":"171","accountcode":"account-9937","effective_caller_id_name":"Amanda Goldner","effective_caller_id_number":"145","outbound_caller_id_name":"Maximilian Heathcote","outbound_caller_id_number":"148","emergency_caller_id_name":"Norris Collier","emergency_caller_id_number":"128","directory_first_name":"Arianna Zieme","directory_last_name":"Hahn","directory_visible":"true","directory_exten_visible":"true","max_registrations":null,"limit_max":"2","limit_destination":"error\/user_busy","missed_call_app":"string","missed_call_data":"string","user_context":"mertz27.com","toll_allow":"international","call_timeout":"30","call_group":"billing","call_screen_enabled":"true","user_record":null,"hold_music":"local_stream:\/\/default","auth_acl":"string","cidr":"string","sip_force_contact":null,"nibble_account":null,"sip_force_expires":null,"mwi_account":"patrick.kerluke@gmail.com","sip_bypass_media":"bypass-media-after-bridge","unique_id":null,"dial_string":"\/location\/of\/the\/endpoint","dial_user":null,"dial_domain":null,"do_not_disturb":null,"forward_all_destination":null,"forward_all_enabled":null,"forward_busy_destination":null,"forward_busy_enabled":null,"forward_no_answer_destination":null,"forward_no_answer_enabled":null,"forward_user_not_registered_destination":null,"forward_user_not_registered_enabled":null,"follow_me_uuid":null,"follow_me_enabled":"string","follow_me_destinations":"string","enabled":"true","description":"Extension created while testing API","absolute_codec_string":"absolute\/codec\/string","force_ping":"false","users":{{"user_uuid":"e20a1239-9bd2-4bb1-ac62-22a828e10f8b","domain_uuid":"9b6787d9-a0c6-484f-8e39-3f64f457db81","username":"jazlyn.hoppe@nitzsche.com","contact_uuid":null,"api_key":null,"user_enabled":"true","add_user":"admin","add_date":"2021-08-30 20:10:57.914963+0000","pivot":{"extension_uuid":"dc2cd102-65be-4779-8a2f-d5769362724e","user_uuid":"e20a1239-9bd2-4bb1-ac62-22a828e10f8b"}}}},{"extension_uuid":"47d8b854-ba17-49ba-8f52-a2c2d2649a90","domain_uuid":"9b6787d9-a0c6-484f-8e39-3f64f457db81","extension":"164","accountcode":"account-6963","effective_caller_id_name":"Joelle Rodriguez","effective_caller_id_number":"144","outbound_caller_id_name":"Otis Shields","outbound_caller_id_number":"144","emergency_caller_id_name":"Ahmad Muller","emergency_caller_id_number":"172","directory_first_name":"D'angelo Dach Sr.","directory_last_name":"Walker","directory_visible":"true","directory_exten_visible":"false","max_registrations":null,"limit_max":"6","limit_destination":"error\/user_busy","missed_call_app":"string","missed_call_data":"string","user_context":"mertz27.com","toll_allow":"international","call_timeout":"30","call_group":"billing","call_screen_enabled":"false","user_record":"local","hold_music":"local_stream:\/\/default","auth_acl":"string","cidr":"string","sip_force_contact":"NDLB-connectile-dysfunction-2.0","nibble_account":null,"sip_force_expires":null,"mwi_account":"ferne.torphy@yahoo.com","sip_bypass_media":null,"unique_id":null,"dial_string":"\/location\/of\/the\/endpoint","dial_user":null,"dial_domain":null,"do_not_disturb":null,"forward_all_destination":null,"forward_all_enabled":null,"forward_busy_destination":null,"forward_busy_enabled":null,"forward_no_answer_destination":null,"forward_no_answer_enabled":null,"forward_user_not_registered_destination":null,"forward_user_not_registered_enabled":null,"follow_me_uuid":null,"follow_me_enabled":"string","follow_me_destinations":"string","enabled":"true","description":"Extension created while testing API","absolute_codec_string":"absolute\/codec\/string","force_ping":"true","users":{{"user_uuid":"e20a1239-9bd2-4bb1-ac62-22a828e10f8b","domain_uuid":"9b6787d9-a0c6-484f-8e39-3f64f457db81","username":"jazlyn.hoppe@nitzsche.com","contact_uuid":null,"api_key":null,"user_enabled":"true","add_user":"admin","add_date":"2021-08-30 20:10:57.914963+0000","pivot":{"extension_uuid":"47d8b854-ba17-49ba-8f52-a2c2d2649a90","user_uuid":"e20a1239-9bd2-4bb1-ac62-22a828e10f8b"}}}}}}),
            )
        ),
    )
     */

     /**
     * Get extension by ID
     *
    @OA\Get(
        tags={"Extension"},
        path="/extension/{uuid}",
        security={{"bearer_auth": {}}},
        x={"route-$action"="getById"},
        @OA\Parameter(ref="#/components/parameters/uuid"),
        @OA\Parameter(ref="#/components/parameters/extension_includes[]"),
        @OA\Parameter(ref="#/components/parameters/limit"),
        @OA\Parameter(ref="#/components/parameters/page"),
        @OA\Response(
            response=200,
            description="Get users list in the current domain",
            @OA\MediaType(
                @OA\Schema(ref="#/components/schemas/ExtensionWithRelatedUsersSchema"),
                mediaType="application/json",
                @OA\Examples(example="/extension/{{extension_uuid}}", summary="", value={"extension_uuid":"47d8b854-ba17-49ba-8f52-a2c2d2649a90","domain_uuid":"9b6787d9-a0c6-484f-8e39-3f64f457db81","extension":"164","accountcode":"account-6963","effective_caller_id_name":"Joelle Rodriguez","effective_caller_id_number":"144","outbound_caller_id_name":"Otis Shields","outbound_caller_id_number":"144","emergency_caller_id_name":"Ahmad Muller","emergency_caller_id_number":"172","directory_first_name":"D'angelo Dach Sr.","directory_last_name":"Walker","directory_visible":"true","directory_exten_visible":"false","max_registrations":null,"limit_max":"6","limit_destination":"error\/user_busy","missed_call_app":"string","missed_call_data":"string","user_context":"mertz27.com","toll_allow":"international","call_timeout":"30","call_group":"billing","call_screen_enabled":"false","user_record":"local","hold_music":"local_stream:\/\/default","auth_acl":"string","cidr":"string","sip_force_contact":"NDLB-connectile-dysfunction-2.0","nibble_account":null,"sip_force_expires":null,"mwi_account":"ferne.torphy@yahoo.com","sip_bypass_media":null,"unique_id":null,"dial_string":"\/location\/of\/the\/endpoint","dial_user":null,"dial_domain":null,"do_not_disturb":null,"forward_all_destination":null,"forward_all_enabled":null,"forward_busy_destination":null,"forward_busy_enabled":null,"forward_no_answer_destination":null,"forward_no_answer_enabled":null,"forward_user_not_registered_destination":null,"forward_user_not_registered_enabled":null,"follow_me_uuid":null,"follow_me_enabled":"string","follow_me_destinations":"string","enabled":"true","description":"Extension created while testing API","absolute_codec_string":"absolute\/codec\/string","force_ping":"true"}),
                @OA\Examples(example="/extension/{{extension_uuid}}?includes[]=users", summary="", value={"extension_uuid":"47d8b854-ba17-49ba-8f52-a2c2d2649a90","domain_uuid":"9b6787d9-a0c6-484f-8e39-3f64f457db81","extension":"164","accountcode":"account-6963","effective_caller_id_name":"Joelle Rodriguez","effective_caller_id_number":"144","outbound_caller_id_name":"Otis Shields","outbound_caller_id_number":"144","emergency_caller_id_name":"Ahmad Muller","emergency_caller_id_number":"172","directory_first_name":"D'angelo Dach Sr.","directory_last_name":"Walker","directory_visible":"true","directory_exten_visible":"false","max_registrations":null,"limit_max":"6","limit_destination":"error\/user_busy","missed_call_app":"string","missed_call_data":"string","user_context":"mertz27.com","toll_allow":"international","call_timeout":"30","call_group":"billing","call_screen_enabled":"false","user_record":"local","hold_music":"local_stream:\/\/default","auth_acl":"string","cidr":"string","sip_force_contact":"NDLB-connectile-dysfunction-2.0","nibble_account":null,"sip_force_expires":null,"mwi_account":"ferne.torphy@yahoo.com","sip_bypass_media":null,"unique_id":null,"dial_string":"\/location\/of\/the\/endpoint","dial_user":null,"dial_domain":null,"do_not_disturb":null,"forward_all_destination":null,"forward_all_enabled":null,"forward_busy_destination":null,"forward_busy_enabled":null,"forward_no_answer_destination":null,"forward_no_answer_enabled":null,"forward_user_not_registered_destination":null,"forward_user_not_registered_enabled":null,"follow_me_uuid":null,"follow_me_enabled":"string","follow_me_destinations":"string","enabled":"true","description":"Extension created while testing API","absolute_codec_string":"absolute\/codec\/string","force_ping":"true","users":{{"user_uuid":"e20a1239-9bd2-4bb1-ac62-22a828e10f8b","domain_uuid":"9b6787d9-a0c6-484f-8e39-3f64f457db81","username":"jazlyn.hoppe@nitzsche.com","contact_uuid":null,"api_key":null,"user_enabled":"true","add_user":"admin","add_date":"2021-08-30 20:10:57.914963+0000","pivot":{"extension_uuid":"47d8b854-ba17-49ba-8f52-a2c2d2649a90","user_uuid":"e20a1239-9bd2-4bb1-ac62-22a828e10f8b"}}}}),
                @OA\Examples(example="/extension/{{extension_uuid}}?includes[]=voicemail", summary="", value={"extension_uuid":"47d8b854-ba17-49ba-8f52-a2c2d2649a90","domain_uuid":"9b6787d9-a0c6-484f-8e39-3f64f457db81","extension":"164","accountcode":"account-6963","effective_caller_id_name":"Joelle Rodriguez","effective_caller_id_number":"144","outbound_caller_id_name":"Otis Shields","outbound_caller_id_number":"144","emergency_caller_id_name":"Ahmad Muller","emergency_caller_id_number":"172","directory_first_name":"D'angelo Dach Sr.","directory_last_name":"Walker","directory_visible":"true","directory_exten_visible":"false","max_registrations":null,"limit_max":"6","limit_destination":"error\/user_busy","missed_call_app":"string","missed_call_data":"string","user_context":"mertz27.com","toll_allow":"international","call_timeout":"30","call_group":"billing","call_screen_enabled":"false","user_record":"local","hold_music":"local_stream:\/\/default","auth_acl":"string","cidr":"string","sip_force_contact":"NDLB-connectile-dysfunction-2.0","nibble_account":null,"sip_force_expires":null,"mwi_account":"ferne.torphy@yahoo.com","sip_bypass_media":null,"unique_id":null,"dial_string":"\/location\/of\/the\/endpoint","dial_user":null,"dial_domain":null,"do_not_disturb":null,"forward_all_destination":null,"forward_all_enabled":null,"forward_busy_destination":null,"forward_busy_enabled":null,"forward_no_answer_destination":null,"forward_no_answer_enabled":null,"forward_user_not_registered_destination":null,"forward_user_not_registered_enabled":null,"follow_me_uuid":null,"follow_me_enabled":"string","follow_me_destinations":"string","enabled":"true","description":"Extension created while testing API","absolute_codec_string":"absolute\/codec\/string","force_ping":"true","voicemail":{"domain_uuid":"9b6787d9-a0c6-484f-8e39-3f64f457db81","voicemail_uuid":"afe1b545-28e6-40b4-87f2-994089584103","voicemail_id":"164","greeting_id":"0","voicemail_alternate_greet_id":"0","voicemail_mail_to":"ctorp@gutkowski.com","voicemail_sms_to":"1-292-343-1434","voicemail_transcription_enabled":"true","voicemail_attach_file":"\/path\/to\/file","voicemail_file":"attach","voicemail_local_after_email":"false","voicemail_enabled":"true","voicemail_description":"Laborum ut et consequuntur quaerat. Aut ut et dolor veritatis nihil laudantium. Nobis maxime non quas quidem assumenda. Consequuntur temporibus debitis molestias delectus animi non omnis.","voicemail_name_base64":null,"voicemail_tutorial":"string"}}),
            )
        ),
    )
    */

/**
     * Extension create
     *
     * Creates an extension and attaches it to a user (optionally)
     *
    @ OA\Post(
        tags={"Extension"},
        path="/extension",
        @ OA\RequestBody(
            required=true,
            @ OA\JsonContent(
                allOf={
                    @ OA\Schema(ref="#/components/schemas/Extension"),
                    @ OA\Schema(@ OA\Property(
                        property="users",
                        type="array",
                        @ OA\Items(
                            allOf={
                                @ OA\Schema(
                                    @ OA\Property(
                                        property="user_uuid",
                                        type="string",
                                        format="uuid",
                                        description="User id's to attach extension to. If not passed, then extension is attached to the current user",
                                    )
                                ),
                            }
                        ),
                    )),
                },
                example={
                    "Create an exnetsion": {},
                    "Create an exnetsion basic example": {
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
            description="Extension created response",
            @ OA\JsonContent(ref="#/components/schemas/Extension"),
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
    public function create(CreateExtensionRequest $request)
    {
        $data = $request->get('extension', []);

        return $this->response($this->extensionService->create($data), 201);
    }

    /**
     * Updates an extension
     *
    @ OA\Put(
        tags={"Extension"},
        path="/extension/{extension_uuid}",
        @ OA\Parameter(ref="#/components/parameters/uuid"),
        @ OA\RequestBody(
            required=true,
            @ OA\JsonContent(
                ref="#/components/schemas/ExtensionCreateSchema",
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
            description="`TODO Stub` Success ...",
            @ OA\JsonContent(ref="#/components/schemas/Extension"),
        ),
        @ OA\Response(response=400, description="`TODO Stub` Could not ..."),
    )
    */
    public function update($extensionId, UpdateExtensionRequest $request)
    {
        $data = $request->get('extension', []);

        $update = $this->extensionService->update($extensionId, $data);

        $return = $this->response($update);

        return $return;
    }

    /**
     * Delets an extension
     *
    @ OA\Delete(
        tags={"Extension"},
        path="/extension/{extension_uuid}",
        @ OA\Parameter(ref="#/components/parameters/uuid"),
        @ OA\Response(
            response=200,
            description="`TODO Stub` Success ...",
            @ OA\JsonContent(
                example={
                    "messages": {
                        "`TODO` Describe response",
                    },
                },
            ),
        ),
        @ OA\Response(response=400, description="`TODO Stub` Could not ..."),
    )
    */
    public function delete($extensionId)
    {
        return $this->response($this->extensionService->delete($extensionId));
    }

}
