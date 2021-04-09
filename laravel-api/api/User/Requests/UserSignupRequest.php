<?php

namespace Api\User\Requests;

use Api\User\Models\User;
use Api\Domain\Models\Domain;
use Illuminate\Validation\Rule;
use Api\Extension\Models\Extension;
use Infrastructure\Http\ApiRequest;
use Infrastructure\Rules\UsernameRule;

class UserSignupRequest extends ApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
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
            ],
            'password' => 'required|min:6|max:25',

            'extensions' => 'required|array',
            'extensions.*.extension' =>
            [
                'required',
                'distinct',
                'integer',
                'min:1',
                'max:999',
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
            'extensions.*.password' => 'required|min:6|max:25',
            'extensions.*.voicemail_password' => 'required|integer',
            'contacts' => 'array',
            'contacts.*.contact_url' => 'url',
        ];
    }
}
