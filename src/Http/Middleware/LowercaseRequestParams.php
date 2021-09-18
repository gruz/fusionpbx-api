<?php

namespace Gruz\FPBX\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TransformsRequest;

class LowercaseRequestParams extends TransformsRequest
{
    /**
     * Transform the given value.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function transform($key, $value)
    {
        $field_names = [
            'domain_name',
            'user_email',
            'username',
        ];

        if ($key === 'username' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return $value;
        }

        return in_array($key, $field_names) ? \strtolower($value) : $value;
    }
}
