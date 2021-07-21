<?php

namespace App\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class LoginRequestWebProv extends UserLoginRequest
{
    protected function prepareForValidation()
    {
        preg_match('/(.*)@(.*)$/', $this->cloud_username, $matches);
        $data = [
            'username' => \Arr::get($matches, 1),
            'password' => $this->cloud_password ? $this->cloud_password : null,
            'domain_name' => \Arr::get($matches, 2),
        ];

        $this->merge($data);

        parent::prepareForValidation();
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = \Arr::flatten($validator->errors()->toArray());
        $errors = implode(PHP_EOL, $errors);

        $xw = xmlwriter_open_memory();
        xmlwriter_set_indent($xw, 1);
        xmlwriter_set_indent_string($xw, ' ');
        xmlwriter_start_document($xw, '1.0', 'UTF-8');

        $row = function ($element, $value)  use ($xw) {
            xmlwriter_start_element($xw, $element);
            xmlwriter_write_cdata($xw,  $value);
            xmlwriter_end_element($xw);
        };

        xmlwriter_start_element($xw, 'error');
        $row('message', $errors);
        xmlwriter_end_element($xw); // error

        xmlwriter_end_document($xw);
        $xml = xmlwriter_output_memory($xw);

        $response = response($xml, 401, [
            'Content-Type' => 'application/xml'
        ]);

        throw new ValidationException($validator, $response);

    }

}
