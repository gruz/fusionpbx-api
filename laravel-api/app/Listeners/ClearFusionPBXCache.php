<?php

namespace App\Listeners;

use App\Services\FreeSwicthHookService;
class ClearFusionPBXCache
{
  public function handle($event)
  {
    $s = new FreeSwicthHookService;
    $response = $s->reload();
    return $response;
  }
}
