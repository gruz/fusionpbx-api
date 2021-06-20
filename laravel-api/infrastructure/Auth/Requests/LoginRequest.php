<?php

namespace Infrastructure\Auth\Requests;

use App\Traits\ApiRequestTrait;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    use ApiRequestTrait;

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

        if (empty($data)) {
            return $data;
        }

        if ($key == 'domain_name' && strpos($data, '.') === false) {
            $data = $data . '.' . config('fpbx.default.domain.mothership_domain');
        }

        return $data;
    }
}
