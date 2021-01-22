<?php

namespace Api\User\Requests;

use Api\User\Models\User;
use Api\User\Models\Domain;
use Illuminate\Support\Arr;
use Infrastructure\Http\ApiRequest;
use Illuminate\Contracts\Validation\Validator;
use Api\User\Exceptions\WrongSignupDataException;

class SignupDomainRequest extends ApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $model = new Domain();

        $rules1 = $this->buildDefaultRules($model);
        $model = new User();
        $rules2 = $this->buildDefaultRules($model);
        dd($rules1, $rules2);

        if (empty(request('team')) && empty(request('user'))) {
            return [
                'team|user' => 'array|required',
            ];
        }

        return [
            // ~ 'team' => 'array|required',
            'team.email' => 'required_with:team|email',
            'team.domain_name' => 'required_with:team|string',
            'team.password' => 'required_with:team|string|min:8',
            'team.username' => 'required_with:team',

            'user.email' => 'required_with:user|email',
            'user.domain_name' => 'required_with:user|string',
            'user.password' => 'required_with:user|string|min:8',
            'user.username' => 'required_with:user',
        ];
    }

    public function attributes()
    {
        return [
            'team|user' => __('team or user data'),
            'team.email' => 'team admin\'s email'
        ];
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
}
