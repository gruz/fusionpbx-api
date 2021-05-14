<?php

namespace App\Listeners;

use Infrastructure\Services\FreeSwicthHookService;

class ClearFusionPBXCache
{
    public function handle($event)
    {
      $service = new FreeSwicthHookService();
      $service->reload();
   }
}