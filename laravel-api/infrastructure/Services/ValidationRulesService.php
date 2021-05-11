<?php

namespace Infrastructure\Services;

use Api\Domain\Models\Domain;
use Illuminate\Validation\Rule;
use Api\Extension\Models\Extension;

/**
 * Class to provide password settings for laravel based on FPBX settings
 *
 * Currently it's only a stub
 * @TODO Implement real code
 */
class ValidationRulesService
{
    public function getPasswordRules($type)
    {
        switch ($type) {
            case 'voicemail':
                $password_rules = 'required|integer';
                break;

            default:
                $password_rules = [
                    'required',
                    'string',
                    'min:8',             // must be at least 10 characters in length
                    'max:255',
                    'regex:/[a-z]/',      // must contain at least one lowercase letter
                    'regex:/[A-Z]/',      // must contain at least one uppercase letter
                    'regex:/[0-9]/',      // must contain at least one digit
                    'regex:/[@$!%*#?&\.]/', // must contain a special character
                ];
                break;
        }

        return $password_rules;
    }

    public function getExtensionRules($domain_name = null)
    {
        $rule =
            [
                'required',
                'distinct',
                'integer',
                'min:' . config('fpbx.extension.min'),
                'max:' . config('fpbx.extension.max'),
            ];

        if (!empty($domain_name)) {
            $rule[] = Rule::unique(Extension::class)->where(function ($query) use ($domain_name) {
                $domain = Domain::where('domain_name', $domain_name)->first();
                if (empty($domain)) {
                    return false;
                }
                return $query->where('domain_uuid', $domain->domain_uuid);
            });
        }
        return $rule;
    }
}
