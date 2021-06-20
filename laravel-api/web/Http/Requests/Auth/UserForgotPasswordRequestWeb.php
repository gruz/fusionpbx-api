<?php

namespace Web\Http\Requests\Auth;

use Api\Domain\Services\DomainService;
use App\Auth\Requests\UserForgotPasswordRequest;

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
