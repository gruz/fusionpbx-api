<?php

namespace Gruz\FPBX\Rules;

use Gruz\FPBX\Models\User;
use Illuminate\Contracts\Validation\Rule;

class UserSignupHashExpiredRule implements Rule
{
    private $user_uuid;

    public function __construct($user_uuid)
    {
        $this->user_uuid = $user_uuid;
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
        $model = User::where([
            ['user_uuid', $this->user_uuid],
            ['user_enabled', 'LIKE' , $value .'::%'],
            ])->first();

        if (empty($model)) {
            return false;
        }

        $user_enabled = $model->getAttribute('user_enabled');
        list($code, $date) = explode('::', $user_enabled);

        $expireDate = \Carbon\Carbon::createFromDate($date)->add(config('fpbx.default.domain.activation_expire'));
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
        return __('Invalid or expired validation code');
    }
}
