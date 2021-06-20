<?php

namespace Api\User\Listeners;

use Api\User\Models\User;
use Api\User\Models\UserSetting;
use App\Services\CGRTService;

class UserWasActivatedCGRTListener
{
    private $cGRTService;

    public function __construct(CGRTService $cGRTService)
    {
        $this->cGRTService = $cGRTService;
    }

    public function handle($event)
    {
        if (!config('fpbx.cgrt.enabled')) {
            return;
        }

        /**
         * @var User
         */
        $user = $event->user;

        $client_added = $this->cGRTService->addClient($user);

        if ($client_added) {
            $account_code = $client_added->account_code;
            $user->extensions()->update(['accountcode' => $account_code]);

            $data = [
                "user_uuid" => $user->user_uuid,
                "domain_uuid" => $user->domain_uuid,
                "user_setting_category" => "payment",
                "user_setting_subcategory" => "account_code",
                "user_setting_name" => "text",
                "user_setting_value" => $account_code,
                "user_setting_order" => 0,
                "user_setting_enabled" => true,
                "user_setting_description" => 'CGRT account code',
            ];
            $user_setting = new UserSetting();
            $user_setting->fillable[] = 'domain_uuid';
            $user_setting->fillable[] = 'user_uuid';
            $user_setting->fill($data);
            $user_setting->save();

            $this->cGRTService->addSIPAccount($user, $client_added);
            $this->cGRTService->assignTariffPlan($client_added->tenant, $client_added->account_code);
        }

    }
}
