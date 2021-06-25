<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Services\Fpbx\UserSettingService;
use App\Services\Fpbx\DomainSettingService;
use App\Services\Fpbx\DefaultSettingService;
use App\Services\Fpbx\DomainService;
use App\Services\Fpbx\UserService;
use Arr;
use Illuminate\Support\Facades\App;
use Str;

class FusionPBXPasswordRequirements implements Rule
{

    protected $appUuid;
    protected $errorMessage;
    protected $domainName;
    protected $userEmail;
    protected $passwordFor;
    protected $passwordSettingNames;

    protected $userSettingService;
    protected $domainSettingService;
    protected $defaultSettingService;

    protected $domainService;
    protected $userService;

    public $messagesText;

    public function __construct(
        string $domainName,
        string $userEmail,
        string $passwordFor
    ) {
        $this->domainName = $domainName;
        $this->userEmail = $userEmail;
        $this->passwordFor = $passwordFor;

        $this->domainService = App::make(DomainService::class);
        $this->userService = App::make(UserService::class);
        $this->userSettingService = App::make(UserSettingService::class);
        $this->domainSettingService = App::make(DomainSettingService::class);
        $this->defaultSettingService = App::make(DefaultSettingService::class);

        $this->messagesText = [
            'label-characters' => __("Characters"),
            'label-numbers' => __("Numbers"),
            'label-uppercase_letters' => __("Uppercase Letters"),
            'label-lowercase_letters' => __("Lowercase Letters"),
            'label-special_characters' => __("Special Characters"),
            'message-password_requirements' => __("Password Requirements")
        ];

        $fPBXDefaultSettings = $this->defaultSettingService->getByAttributes([
            'default_setting_category' => $this->passwordFor
        ]);
        $this->appUuid = $fPBXDefaultSettings->pluck('app_uuid')->first();
        $this->passwordSettingNames = $fPBXDefaultSettings
            ->filter(function ($defaultSetting) {
                return Str::contains($defaultSetting->default_setting_subcategory, 'password_');
            })
            ->pluck('default_setting_subcategory')
            ->toArray();
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $arrayOfSettings = [];
        $res[$this->passwordFor] = [];

        // get domain name and email to get settings data
        $domain = $this->domainService->getByAttributes(['domain_name' => $this->domainName])->first();
        $user = $this->userService->getByAttributes(['user_email' => $this->userEmail])->first();
        if (is_null($domain) || is_null($user)) {
            $this->errorMessage = __("Invalid data");
            return false;
        }

        $domain_uuid = $domain->domain_uuid;
        $arrayOfSettings['user'] = $this->userSettingService
            ->getByAttributes([
                'domain_uuid' => $domain_uuid,
                'user_uuid' => $user->user_uuid,
                'user_setting_category' => $this->passwordFor,
                'user_setting_subcategory' => $this->passwordSettingNames,
                'user_setting_enabled' => true
            ])
            ->map/*(function($setting) { return $setting->toArray(); })*/
            ->only([
                'user_setting_subcategory',
                'user_setting_name',
                'user_setting_value'
            ])
            ->all();

        $arrayOfSettings['domain'] = $this->domainSettingService
            ->getByAttributes([
                'domain_uuid' => $domain_uuid,
                'domain_setting_category' => $this->passwordFor,
                'domain_setting_subcategory' => $this->passwordSettingNames,
                'domain_setting_enabled' => true
            ])
            ->map/*(function($setting) { return $setting->toArray(); })*/
            ->only([
                'domain_setting_subcategory',
                'domain_setting_name',
                'domain_setting_value'
            ])
            ->all();

        $arrayOfSettings['default'] = $this->defaultSettingService
            ->getByAttributes([
                'app_uuid' => $this->appUuid,
                'default_setting_category' => $this->passwordFor,
                'default_setting_subcategory' => $this->passwordSettingNames,
                'default_setting_enabled' => true
            ])
            ->map/*(function($setting) { return $setting->toArray(); })*/
            ->only([
                'default_setting_subcategory',
                'default_setting_name',
                'default_setting_value'
            ])
            ->all();

        // $passwordSettingNames = $this->defaultSettingService
        //                              ->getByAttributes([
        //                                  'default_setting_category' => $this->passwordFor])
        //                              ->filter(function($defaultSetting) { 
        //                                 return Str::contains($defaultSetting->default_setting_subcategory, 'password_'); })
        //                              ->pluck('default_setting_subcategory')
        //                              ->toArray();

        // $userPasswordSettings = $this->userSettingService
        //                              ->getByAttributes([
        //                                  'domain_uuid' => $domain_uuid,
        //                                  'user_uuid' => $user->user_uuid, // $user->user_uuid
        //                                  'user_setting_category' => $this->passwordFor,
        //                                  'user_setting_subcategory' => $this->passwordSettingNames,
        //                                  'user_setting_enabled' => true])
        //                             ->all();

        // /** @var User_setting $userSetting */
        // foreach ($userPasswordSettings as $userSetting ) {
        //     $requirement = $this->passwordFor . '.' .
        //                    $userSetting->user_setting_subcategory . '.' .
        //                    $userSetting->user_setting_name;

        //     if (!Arr::has($res, $requirement)) {
        //         Arr::set($res, $requirement, $userSetting->user_setting_value);
        //     }
        // }

        // $domainPasswordSettings = $this->domainSettingService
        //                                ->getByAttributes([
        //                                    'domain_uuid' => $domain_uuid,
        //                                    'domain_setting_category' => $this->passwordFor,
        //                                    'domain_setting_subcategory' => $this->passwordSettingNames,
        //                                    'domain_setting_enabled' => true])
        //                                ->all();

        // /** @var Domain_setting $domainSetting */
        // foreach ($domainPasswordSettings as $domainSetting ) {
        //     $requirement = $this->passwordFor . '.' .
        //                    $domainSetting->domain_setting_subcategory . '.' .
        //                    $domainSetting->domain_setting_name;

        //     if (!Arr::has($res, $requirement)) {
        //         Arr::set($res, $requirement, $domainSetting->domain_setting_value);
        //     }
        // }                               

        // $defaultPasswordSettings = $this->defaultSettingService
        //                                 ->getByAttributes([
        //                                     'app_uuid' => $this->USERS_APP_UUID,
        //                                     'default_setting_category' => $this->passwordFor,
        //                                     'default_setting_subcategory' => $this->passwordSettingNames,
        //                                     'default_setting_enabled' => true])
        //                                 ->all();

        // /** @var Default_setting $defaultSetting */
        // foreach ($defaultPasswordSettings as $defaultSetting ) {
        //     $requirement = $this->passwordFor . '.' .
        //                    $defaultSetting->default_setting_subcategory . '.' .
        //                    $defaultSetting->default_setting_name;

        //     if (!Arr::has($res, $requirement)) {
        //         Arr::set($res, $requirement, $defaultSetting->default_setting_value);
        //     }
        // }                                       



        // Arr::pluck | Arr::get() | Arr::add() | Arr::collapse()
        // subcategory | type | value
        // $concated = $userPasswordSettings->concat($domainPasswordSettings)->concat($defaultPasswordSettings);

        // $res[$this->passwordFor] = $concated->map(function ($item, $key) use ($res) {
        //     $res[$this->passwordFor] = $item;
        //     // $res[$this->passwordFor][$item['$item_setting_subcategory'][$item_setting_name] = $item_setting_value;
        // });


        $_SESSION = $this->createPasswordRequiremnents($arrayOfSettings);
        // $_SESSION = $res;
        // This is needed to bypass the FusionPBX Session check
        $_SESSION['messages'] = ['negative' => ['message' => []], 'positive' => ['message' => []]];

        // Here we use singular helper because in FusionPBX password strength check function
        // values of setting category in singular form but in DB they are in plural form 
        error_reporting(E_ALL & ~E_NOTICE);
        $result = \check_password_strength($value, $this->messagesText, Str::singular($this->passwordFor));
        $this->errorMessage = $_SESSION['message'] ? $_SESSION['message'] : '';
        error_reporting(-1);

        return $result;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->errorMessage;
    }

    protected function createPasswordRequiremnents(array $settings)
    {
        $res = [];
        foreach ($settings as $settingType => $settingsConfigurations) {
            foreach ($settingsConfigurations as $setting) {
                $requirement = Str::singular($this->passwordFor) . '.' .
                    $setting[$settingType . '_setting_subcategory'] . '.' .
                    $setting[$settingType . '_setting_name'];
                if (!Arr::has($res, $requirement)) {
                    Arr::set($res, $requirement, $setting[$settingType . '_setting_value']);
                }
            }
        }

        return $res;
    }
}
