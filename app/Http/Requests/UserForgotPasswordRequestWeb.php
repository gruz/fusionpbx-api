<?php

namespace App\Http\Requests;

use Gruz\FPBX\Services\Fpbx\DomainService;
use Gruz\FPBX\Requests\UserForgotPasswordRequest;

class UserForgotPasswordRequestWeb extends UserForgotPasswordRequest
{
    protected function prepareForValidation()
    {
        if (!empty($this->domain_name)) {
            return;
        }
        /**
         * @var DomainService
         */
        $domainService = app(DomainService::class);
        $domainModel = $domainService->getSystemDomain();
        $this->domain_name = $domainModel->getAttribute('domain_name');

        $this->merge([
            'domain_name' => $this->domain_name,
        ]);
    }
}
