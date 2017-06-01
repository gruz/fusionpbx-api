<?php

namespace Infrastructure\Auth\Requests;

use Infrastructure\Http\ApiRequest;

class LoginRequest extends ApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'username'    => 'required',
            'domain_name'    => 'required',
            'password' => 'required'
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
