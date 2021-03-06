<?php

namespace Api\Extension\Requests;

use Api\Extension\Models\Extension;
use Infrastructure\Http\ApiRequest;

class UpdateExtensionRequest extends ApiRequest
{
    public function authorize()
    {
        $extensionId = request()->route()->parameter('id');
        $extension = Extension::find($extensionId);

        $userCanUpdate = $this->user()->can('update', $extension);
        $userCanUpdate = true;

        return $extension && $userCanUpdate;
    }

    public function rules()
    {
        $model = new Extension();
        $rules = $this->buildDefaultRules($model);

        return $rules;
    }

    public function attributes()
    {
        return [
            'extension.domain_uuid' => 'domain uuid'
        ];
    }
}
