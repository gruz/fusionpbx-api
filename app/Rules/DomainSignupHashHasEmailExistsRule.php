<?php

namespace App\Rules;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Contracts\Validation\Rule;

class DomainSignupHashHasEmailExistsRule implements Rule
{
    private $field;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($field)
    {
        //
        $this->field = $field;
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
        $hash = $this->field;

        if (!Str::isUuid($hash)) {
            return false;
        }

        $model = new \App\Models\PostponedAction;

        $count = $model
            ->where('hash', $hash )
            ->whereJsonContains('request->users', [
            [ "user_email" => $value ]
        ])->count();

        return (bool) $count;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Activation link for your email not found';
    }
}
