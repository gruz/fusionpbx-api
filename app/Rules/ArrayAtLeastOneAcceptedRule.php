<?php

namespace App\Rules;

use Illuminate\Support\Arr;
use Illuminate\Contracts\Validation\Rule;

class ArrayAtLeastOneAcceptedRule implements Rule
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
        // d($attribute, $value);
        foreach ($value as $arrayElement) {
            $result = Arr::get($arrayElement, $this->field, false);
            if ($result) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'At least one `' . $this->field . '` to be true is needed';
    }
}
