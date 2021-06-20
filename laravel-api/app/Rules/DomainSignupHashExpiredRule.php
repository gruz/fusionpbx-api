<?php

namespace App\Rules;

use Illuminate\Support\Str;
use Illuminate\Contracts\Validation\Rule;
use Api\PostponedAction\Models\PostponedAction;

class DomainSignupHashExpiredRule implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (app()->runningUnitTests()) {
            // Ugly way to get data from another rule when testing as request is flushed
            $GLOBALS['test_signup_hash'] = $value;
        }

        // if (!Str::isUuid($value)) {
        //     return false;
        // }

        $model = PostponedAction::where('hash', $value)->first();

        if (empty($model)) {
            return false;
        }

        $expireDate = $model->created_at->add(config('fpbx.default.domain.activation_expire'));
        $now = \Carbon\Carbon::now();

        if ($now > $expireDate) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Domain activation link expired';
    }
}
