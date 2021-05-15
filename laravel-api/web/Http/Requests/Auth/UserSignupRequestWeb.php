<?php

namespace Web\Http\Requests\Auth;

use Api\Domain\Services\DomainService;
use Api\Extension\Services\ExtensionService;
use Infrastructure\Auth\Requests\UserSignupRequest;

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

        $extension = app(ExtensionService::class)->getMaxExtension($this->domain_uuid);
        $min_extension = config('fpbx.extension.min');

        if ($extension < $min_extension) {
            $extension = $min_extension;
        } else {
            $extension++;
        }

        $this->merge([
            'domain_name' => $this->domain_name,
            'extensions' => [
                [
                    'extension' => $extension,
                    'password' => $this->password,
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
}
