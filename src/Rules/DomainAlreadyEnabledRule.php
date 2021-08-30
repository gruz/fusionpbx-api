<?php

namespace Gruz\FPBX\Rules;

use Illuminate\Contracts\Validation\Rule;
use Gruz\FPBX\Models\PostponedAction;
use Gruz\FPBX\Services\Fpbx\DomainService;

class DomainAlreadyEnabledRule implements Rule
{

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $model = PostponedAction::where('code', $value)->first();

        if (empty($model)) {
            return false;
        }

        /**
         * @var DomainService
         */
        $domainService = app(DomainService::class);


        $domainModel = $domainService->getDomainByName($model->request['request']['domain_name']);

        if (empty($domainModel)) {
            return true;
        }

        if ($domainModel->domain_enabled === true || $domainModel->domain_enabled === "true") {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('Domain already activated');
    }
}
