<?php

namespace Infrastructure\Listeners;

use Infrastructure\Services\FreeSwicthHookService;
class ClearFusionPBXCache
{
  public function handle($event)
  {
    $s = new FreeSwicthHookService;
    $response = $s->reload();
    return $response;
  }
}
