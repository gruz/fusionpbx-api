<?php

namespace Api\Pushtoken\Requests;

use Infrastructure\Http\ApiRequest;
use Illuminate\Support\Arr;
use Illuminate\Contracts\Validation\Validator;
use Api\Pushtoken\Exceptions\WrongPushtokenDataException;
use Api\Pushtoken\Exceptions\InvalidPushtokenTypeException;
use Api\Pushtoken\Exceptions\InvalidPushtokenClassException;

class CreatePushtokenRequest extends ApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'token_type' => 'required|string',
            'token' => 'required|string',
            'token_class' => 'required|string',
        ];
    }

    public function attributes()
    {
        return [
            'token_type' => __('token type, `production` or `sandbox`'),
            'token' => __('device push token'),
            'token_class' => __('token class, `voip` or `text`'),
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

        if (empty($data))
        {
          return $data;
        }

        $data = Arr::only($data, ['token_type', 'token', 'token_class']);
        $data = array_map('trim', $data);

        $data['token'] = preg_replace("/[^0-9a-zA-Z]/","",$data['token']);

        if (empty($data['token_type']) || !in_array($data['token_type'], ['production', 'sandbox']))
        {
          throw new InvalidPushtokenTypeException();
        }

        if (empty($data['token_class']) || !in_array($data['token_class'], ['voip', 'text']))
        {
          throw new InvalidPushtokenClassException();
        }

        return $data;
    }

    protected function failedValidation(Validator $validator)
    {
        throw new WrongPushtokenDataException($validator->errors()->toJson());
    }
}
