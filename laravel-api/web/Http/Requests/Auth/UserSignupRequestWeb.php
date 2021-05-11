<?php

namespace Web\Http\Requests\Auth;

use Api\Domain\Models\Domain;
use Api\Extension\Services\ExtensionService;
use Infrastructure\Auth\Requests\UserSignupRequest;

class UserSignupRequestWeb extends UserSignupRequest
{
    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $extension = app(ExtensionService::class)->getMaxExtension($this->domain_uuid);
        $min_extension = config('fpbx.extension.min');

        if ($extension < $min_extension) {
            $extension = $min_extension;
        } else {
            $extension++;
        }

        $domain_name = Domain::where('domain_uuid', $this->domain_uuid)->first()->getAttribute('domain_name');

        $this->merge([
            'domain_name' => $domain_name,
            'extensions' => [
                [
                    'extension' => $extension,
                    'password' => $this->password,
                    'voicemail_password' => $this->voicemail_password,
                    'effective_caller_id_name' => $this->effective_caller_id_name,
                    'effective_caller_id_number' => $extension,
                    'force_ping' => true,
                ]
            ],
        ]);
    }

    public function rules()
    {
        $rules = parent::rules();

        $rules['captcha'] = 'required|captcha';


        return $rules;
    }
}
