<?php

namespace Gruz\FPBX\Listeners;

use Gruz\FPBX\Models\User;
use Gruz\FPBX\Models\UserSetting;
use Gruz\FPBX\Services\CGRTService;

class UserWasActivatedCGRTListener
{
    public function handle($event)
    {
        if (!config('fpbx.cgrt.enabled')) {
            return;
        }

        /**
         * @var CGRTService
         */
        $cGRTService = app(CGRTService::class);

        /**
         * @var User
         */
        $user = $event->user;

        $client_added = $cGRTService->addClient($user);

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

            $cGRTService->addSIPAccount($user, $client_added);
            $cGRTService->assignTariffPlan($client_added->tenant, $client_added->account_code);
        }

    }
}
