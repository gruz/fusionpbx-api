<?php

namespace Gruz\FPBX\Rules;

use Gruz\FPBX\Models\User;
use Gruz\FPBX\Models\Domain;
use Illuminate\Contracts\Validation\Rule;

class UserEmailExistsInDomainRule implements Rule
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
        $domain_name = $this->field;
        $domainModel = Domain::where('domain_name', $domain_name)->first();
        if (empty($domainModel)) {
            return false;
        }

        $usersModel = User::where([
            ['domain_uuid', $domainModel->domain_uuid],
            [$attribute, $value],
        ])->get();

        if (empty($usersModel)) {
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
        return __('User email not found');
    }
}