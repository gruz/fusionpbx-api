<?php

namespace Api\User\Requests;

use Infrastructure\Http\ApiRequest;

class UserForgotPasswordRequest extends ApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'domain_name'    => 'required',
            'user_email' => 'required|email'
        ];
    }

    /**
     * Override get method to determine domain automatically
     *
     * @param   string  $key
     * @param   mixed   $default
     *
     * @return   mixed
     */
    public function get($key, $default = null)
    {
        $data = parent::get($key, $default);

        if (empty($data))
        {
          return $data;
        }

        if ($key == 'domain_name' && strpos($data, '.') === false)
        {
          $data = $data . '.' . env('MOTHERSHIP_DOMAIN');
        }

        return $data;
    }
}
