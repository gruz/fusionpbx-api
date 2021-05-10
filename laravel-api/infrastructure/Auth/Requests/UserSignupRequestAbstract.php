<?php

namespace Infrastructure\Auth\Requests;

use Api\User\Models\User;
use Api\Domain\Models\Domain;
use Illuminate\Validation\Rule;
use Api\Extension\Models\Extension;
use Infrastructure\Rules\UsernameRule;
use Api\Settings\Models\DefaultSetting;
use Illuminate\Foundation\Http\FormRequest;

class UserSignupRequestAbstract extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $password_rule = [
            'required',
            'string',
            'min:8',             // must be at least 10 characters in length
            'max:255',
            'regex:/[a-z]/',      // must contain at least one lowercase letter
            'regex:/[A-Z]/',      // must contain at least one uppercase letter
            'regex:/[0-9]/',      // must contain at least one digit
            'regex:/[@$!%*#?&]/', // must contain a special character
        ];
        $rules = [
            'domain_name' => [
                'required',
                Rule::exists(Domain::class, 'domain_name')
                ->where('domain_enabled', true),
            ],
            'user_email' => [
                'required',
                Rule::unique(User::class)->where(function ($query) {
                    // $domain_name = $this->request->get('domain_name');
                    $domain_name = request()->get('domain_name');

                    $domain = Domain::where('domain_name', $domain_name)
                        ->where('domain_enabled', true)
                        ->first();

                    if (empty($domain)) {
                        return false;
                        // return $query->where('domain_uuid', 'fake');
                    }

                    return $query->where('domain_uuid', $domain->domain_uuid);
                }),
            ],
            'username' => [
                'required',
                'string',
                'max:255',
                new UsernameRule(),
                Rule::unique(User::class)->where(function ($query) {
                    // $domain_name = $this->request->get('domain_name');
                    $domain_name = request()->get('domain_name');

                    $domain = Domain::where('domain_name', $domain_name)
                        ->where('domain_enabled', true)
                        ->first();

                    if (empty($domain)) {
                        return false;
                        // return $query->where('domain_uuid', 'fake');
                    }

                    return $query->where('domain_uuid', $domain->domain_uuid);
                }),
            ],

            'password' => $password_rule,

            'extensions' => 'required|array',
            'extensions.*.extension' =>
            [
                'required',
                'distinct',
                'integer',
                'min:' . config('fpbx.extension.min'),
                'max:' . config('fpbx.extension.max'),
                Rule::unique(Extension::class)->where(function ($query) {
                    // $domain_name = $this->request->get('domain_name');
                    $domain_name = request()->get('domain_name');

                    $domain = Domain::where('domain_name', $domain_name)->first();
                    if (empty($domain)) {
                        return false;
                    }
                    return $query->where('domain_uuid', $domain->domain_uuid);
                }),
            ],
            'extensions.*.password' => $password_rule,
            'extensions.*.voicemail_password' => 'required|integer',
            'contacts' => 'array',
            'contacts.*.contact_url' => 'url',
        ];

        $resellerCodeRequired = config('fpbx.resellerCodeRequired');
        if ($resellerCodeRequired) {
            $rules['reseller_reference_code'] = [
                'required',
                Rule::exists(DefaultSetting::class, 'default_setting_value')
                    ->where('default_setting_category', 'billing')
                    ->where('default_setting_subcategory', 'reseller_code'),
            ];
        }

        return $rules;
    }
}
