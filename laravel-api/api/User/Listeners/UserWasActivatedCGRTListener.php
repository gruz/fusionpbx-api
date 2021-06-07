<?php

namespace Api\User\Listeners;

use Infrastructure\Services\CGRTService;

class UserWasActivatedCGRTListener
{
    private $cGRTService;

    public function __construct(CGRTService $cGRTService)
    {
        $this->cGRTService = $cGRTService;
    }

    public function handle($event)
    {
        if (!config('fpbx.cgrt.enabled')) {
            return;
        }

        $client_added = $this->cGRTService->addClient($event->user);
        $this->cGRTService->addSIPAccount($event->user, $client_added);
        $this->cGRTService->assignTariffPlan($client_added);
    }
}
