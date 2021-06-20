<?php

namespace Api\Status\Requests;

use Illuminate\Support\Arr;
use App\Traits\ApiRequestTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Api\Status\Exceptions\WrongStatusDataException;

class SetStatusRequest extends FormRequest
{
    use ApiRequestTrait;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'status_lifetime' => 'integer',
            'os' => 'string',
            'user_status' => 'required|string',
            'services' => 'array',
        ];
    }

    public function attributes()
    {
        return [
            'status_lifetime' => __('should be integer in seconds'),
            'os' => __('available os_type values [' . implode(',' , config('api.OSes')) . ']'),
            'user_status' => __('available statuses [' . implode(',' , config('api.statuses')) . ']'),
            'services' => __('available services [' . implode(',' , config('api.services')) . ']'),
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

        $data = Arr::only($data, ['status_lifetime', 'os', 'user_status', 'services']);

        array_walk_recursive($data, function(& $value){
            $value = trim($value);
        });

        if (empty($data['status_lifetime']))
        {
          $data['status_lifetime'] = config('api.status_lifetime');
        }

        /*
        if (empty($data['os']) || !in_array($data['os'], config('api.OSes')))
        {
          if (empty($data['os']))
          {
            $data['os'] = null;
          }

          throw new InvalidStatusOSException(['os' => $data['os']]);
        }
        */

        if (empty($data['user_status']) || !in_array($data['user_status'],  array_keys(config('api.statuses'))))
        {
          if (empty($data['user_status']))
          {
            $data['user_status'] = 'offline';
          }

          // ~ throw new InvalidStatusException(['status' => $data['status'], 'available_statuses' => implode(', ', array_keys(config('api.statuses')))]);
        }

        /*
        if (empty($data['services']))
        {
          throw new InvalidServiceListException();
        }
        */

        return $data;
    }

    protected function failedValidation(Validator $validator)
    {
        throw new WrongStatusDataException($validator->errors()->toJson());
    }
}
