<?php

namespace Api\Dialplan\Requests;

use Illuminate\Support\Arr;
use Infrastructure\Http\ApiRequestTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Api\Dialplan\Exceptions\WrongPushtokenDataException;
use Api\Dialplan\Exceptions\InvalidPushtokenTypeException;
use Api\Dialplan\Exceptions\InvalidPushtokenClassException;

class CreatePushtokenRequest extends FormRequest
{
    use ApiRequestTrait;
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
      return[];
        return [
            'pushtoken.token_type' => 'required|string',
            'pushtoken.token' => 'required|string',
            'pushtoken.token_class' => 'required|string',
        ];
    }

    public function attributes()
    {
        return [
            'pushtoken.token_type' => __('token type, `production` or `sandbox`'),
            'pushtoken.token' => __('device push token'),
            'pushtoken.token_class' => __('token class, `voip` or `text`'),
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
