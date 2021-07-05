<?php

namespace App\Requests;

use App\Services\Fpbx\DomainService;
use App\Services\Fpbx\ExtensionService;
use App\Requests\UserSignupRequest;

class UserSignupRequestWeb extends UserSignupRequest
{
    private $onlyDomain = false;
    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        /**
         * @var DomainService
         */
        $domainService = app(DomainService::class);
        if (empty($this->domain_name)) {
            $domainModel = $domainService->getSystemDomain();

            $this->domain_name = $domainModel->getAttribute('domain_name');
        } else {
            $domainModel = $domainService->getByAttributes(['domain_name' => $this->domain_name, 'domain_enabled' => true])->first();
        }

        $this->domain_uuid = optional($domainModel)->domain_uuid;

        if (empty($this->domain_uuid)) {
            $extension = null;
            $this->replace(['domain_name' => $this->domain_name]);
            $this->onlyDomain = true;
            return;
        }

        /**
         * @var ExtensionService
         */
        $extension = app(ExtensionService::class)->getNewExtension($this->domain_uuid);

        $s = \Arr::random(['!', '@', '#', '$', '%', '^', '&', '*']);
        $extension_password = str_shuffle(\Str::random(8) . rand(0,9) . $s);

        $this->merge([
            'username' => $this->user_email,
            'domain_name' => $this->domain_name,
            'extensions' => [
                [
                    'extension' => $extension,
                    'password' => $extension_password,
                    'voicemail_password' => $this->voicemail_password,
                    'effective_caller_id_name' => $this->effective_caller_id_name,
                    'effective_caller_id_number' => $extension,
                    'force_ping' => 'true',
                ]
            ],
        ]);
    }

    public function rules()
    {
        $rules = parent::rules();

        $rules['voicemail_password'] = $rules['extensions.*.voicemail_password'];

        if ($this->onlyDomain) {
            $rules = [
                'domain_name' => $rules['domain_name'],
            ];
        }

        if (config('fpbx.captcha_enabled')) {
            $rules['captcha'] = 'required|captcha';
        }

        return $rules;
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'voicemail_password' => __('Voicemail password'),
            'user_email' => __('E-Mail Address'),
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {

        $messages = parent::messages();

        $messages = array_merge($messages, [
            'extensions.0.password.regex' => false,
            'extensions.*.voicemail_password.digits_between' => false,
            // 'password.regex' => __('Min 6 symbols, case sensitive, at least one lowercase, one uppercase and one digit'),
        ]);

        return $messages;
    }
}
