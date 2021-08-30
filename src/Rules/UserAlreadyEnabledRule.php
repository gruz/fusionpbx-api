<?php

namespace Gruz\FPBX\Rules;

use Gruz\FPBX\Models\User;
use Illuminate\Support\Facades\DB;
use Gruz\FPBX\Models\PostponedAction;
use Illuminate\Contracts\Validation\Rule;
use Gruz\FPBX\Services\Fpbx\DomainService;

class UserAlreadyEnabledRule implements Rule
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
            ['user_enabled', 'true'],
        ])->first();

        if (!empty($model)) {
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
        return __('User already enabled');
    }
}
