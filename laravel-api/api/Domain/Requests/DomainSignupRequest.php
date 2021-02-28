<?php

namespace Api\Domain\Requests;

use Api\User\Models\User;
use Illuminate\Support\Arr;
use Api\Domain\Models\Domain;
use Infrastructure\Rules\Hostname;
use Infrastructure\Http\ApiRequest;
use Illuminate\Contracts\Validation\Validator;
use Api\User\Exceptions\WrongSignupDataException;
use Infrastructure\Rules\ArrayAtLeastOneAccepted;

class DomainSignupRequest extends ApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $is_subdomain = $this->request->get('is_subdomain');

        \Illuminate\Support\Facades\Validator::extend('check_array', function ($attribute, $value, $parameters, $validator) {
            return count(array_filter($value, function($var) use ($parameters) { return ( $var && $var >= $parameters[0]); }));
        });

        $rules = [
            'domain_name' => [ 
                'required', 
                'unique:Api\\Domain\\Models\\Domain,domain_name'
            ],
            'users' => 'required',
            'users.*.username' => 'required|distinct',
            'users.*.user_email' => 'required|distinct:ignore_case|email',
            'users.*.password' => 'required|min:6|max:25',
            // 'users.*.is_admin' => 'present|required|min:6|max:25',
            'users' => new ArrayAtLeastOneAccepted('is_admin'),
        ];

        if (!$is_subdomain) {
            $rules['domain_name'][] = new Hostname();
        }

        return $rules;
    }

    /**
     * Override get method to return only needed parameters
     *
     * See https://stackoverflow.com/questions/44127826/laravel-limit-formrequest-to-certain-parameters/44127982?noredirect=1#comment75278940_44127982
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

        $password = $data['password'];
        $data = Arr::only($data, ['email', 'domain_name', 'username']);
        $data = array_map('trim', $data);
        $data['password'] = $password;

        if (strpos($data['domain_name'], '.') === false) {
            $data['domain_name'] = $data['domain_name'] . '.' . env('MOTHERSHIP_DOMAIN');
        }

        $pattern = '~^([a-zA-Z0-9\-]+(\.[a-zA-Z0-9\-]+)+.*)$~';

        if (!preg_match($pattern, $data['domain_name'])) {
            throw new WrongSignupDataException(__('Not a valid URL `:url`', ['url' => $data['domain_name']]));
        }

        return $data;
    }

    protected function failedValidation(Validator $validator)
    {
        throw new WrongSignupDataException($validator->errors()->toJson());
    }

    public function all($keys = null)
    {
        $data = parent::all($keys);

        $is_subdomain = Arr::get($data, 'is_subdomain', config('fpbx.default.domain.new_is_subdomain'));

        if ($is_subdomain) {
            $data['domain_name'] = $data['domain_name'] . '.' . config('fpbx.default.domain.mothership_domain');
        }

        if (!config('fpbx.domain.enabled')) {
            $data['domain_enabled'] = false;
        } else {
            $data['domain_enabled'] = Arr::get($data, 'domain_enabled', config('fpbx.domain.enabled'));
        }

        $data['domain_description'] =  Arr::get($data, 'domain_description', config('fpbx.domain.description') );

        return $data;
    }
}
